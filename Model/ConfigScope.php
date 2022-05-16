<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Cache\LockGuardedCacheLoader;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use SoftCommerce\ProfileConfig\Model\ResourceModel\GetConfigDataInterface;

/**
 * @inheritDoc
 */
class ConfigScope implements ConfigScopeInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $dataScopes;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var Fallback
     */
    private $fallback;

    /**
     * @var GetConfigDataInterface
     */
    private $getConfigData;

    /**
     * @var LockGuardedCacheLoader
     */
    private $lockQuery;

    /**
     * @var int|null
     */
    private $profileId;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param CacheInterface $cache
     * @param Converter $converter
     * @param Encryptor $encryptor
     * @param Fallback $fallback
     * @param GetConfigDataInterface $getConfigData
     * @param LockGuardedCacheLoader $lockQuery
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cache,
        Converter $converter,
        Encryptor $encryptor,
        Fallback $fallback,
        GetConfigDataInterface $getConfigData,
        LockGuardedCacheLoader $lockQuery,
        ScopeCodeResolver $scopeCodeResolver,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->converter = $converter;
        $this->encryptor = $encryptor;
        $this->fallback = $fallback;
        $this->getConfigData = $getConfigData;
        $this->lockQuery = $lockQuery;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function get(
        int $profileId,
        ?string $path = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $this->setProfileId($profileId);
        return $this->getConfigData($this->generatePath($scope, $path, $scopeCode));
    }

    /**
     * @inheritDoc
     */
    public function clean(): void
    {
        $this->data = [];
        $this->scopeCodeResolver->clean();
        $dataCleaner = function () {
            $this->cache->clean([self::CACHE_TAG]);
        };

        $this->lockQuery->lockedCleanData(
            self::LOCK_ID,
            $dataCleaner
        );
    }

    /**
     * @param string $path
     * @return array|int|string|mixed|null
     * @throws \Exception
     */
    public function getConfigData(string $path = '')
    {
        if ($path === '') {
            $this->data[$this->getProfileId()] = array_replace_recursive(
                $this->getData(),
                $this->data[$this->getProfileId()] ?? []
            );
            return $this->data[$this->getProfileId()];
        }

        return $this->getDataByPath($path);
    }

    /**
     * @return array|mixed|null
     * @throws \Exception
     */
    private function getData()
    {
        $dataLoader = function () {
            if ($data = $this->cache->load($this->getCacheId())) {
                return $this->serializer->unserialize($this->encryptor->decrypt($data));
            }
            return false;
        };

        return $this->lockQuery->lockedLoadData(
            self::LOCK_ID,
            $dataLoader,
            \Closure::fromCallable([$this, 'initConfigData']),
            \Closure::fromCallable([$this, 'cacheConfigData'])
        );
    }

    /**
     * @param string $path
     * @return array|string|int|mixed|null
     * @throws \Exception
     */
    private function getDataByPath(string $path)
    {
        $path = explode('/', $path);
        if (count($path) === 1 && current($path) !== ScopeInterface::SCOPE_DEFAULT) {
            if (!isset($this->data[$this->getProfileId()][current($path)])) {
                $data = $this->initConfigData();
                $this->data[$this->getProfileId()] = array_replace_recursive(
                    $data,
                    $this->data[$this->getProfileId()] ?? []
                );
            }
            return $this->data[$this->getProfileId()][current($path)];
        }

        $scope = array_shift($path);
        if ($scope === ScopeInterface::SCOPE_DEFAULT) {
            if (!isset($this->data[$this->getProfileId()][$scope])) {
                $this->data[$this->getProfileId()] = array_replace_recursive(
                    $this->getDataByScope($scope),
                    $this->data[$this->getProfileId()] ?? []
                );
            }
            return $this->getDataByPathKeys($this->data[$this->getProfileId()][$scope] ?? [], $path);
        }

        $scopeId = array_shift($path);
        if (!isset($this->data[$this->getProfileId()][$scope][$scopeId])) {
            $this->data[$this->getProfileId()] = array_replace_recursive(
                $this->getDataByScope($scope, $scopeId),
                $this->data[$this->getProfileId()] ?? []
            );
        }

        return isset($this->data[$this->getProfileId()][$scope][$scopeId])
            ? $this->getDataByPathKeys($this->data[$this->getProfileId()][$scope][$scopeId], $path)
            : null;
    }

    /**
     * @param string $scope
     * @param null $scopeId
     * @return array
     * @throws \Exception
     */
    private function getDataByScope(string $scope, $scopeId = null)
    {
        if (null === $scopeId) {
            $dataLoader = function () use ($scope) {
                if ($data = $this->cache->load($this->getCacheId() . '_' . $scope)) {
                    return [$scope => $this->serializer->unserialize($this->encryptor->decrypt($data))];
                }
                return false;
            };
        } else {
            $dataLoader = function () use ($scope, $scopeId) {
                if ($data = $this->cache->load($this->getCacheId() . '_' . $scope . '_' . $scopeId)) {
                    return [$scope => [$scopeId => $this->serializer->unserialize($this->encryptor->decrypt($data))]];
                }

                if (null === $this->dataScopes
                    && $dataScopes = $this->cache->load($this->getCacheId() . '_scopes')
                ) {
                    $this->dataScopes = $this->serializer->unserialize($this->encryptor->decrypt($dataScopes));
                }

                if (!is_array($this->dataScopes) || isset($this->dataScopes[$scope][$scopeId])) {
                    return false;
                }

                return [$scope => [$scopeId => []]];
            };
        }

        return $this->lockQuery->lockedLoadData(
            self::LOCK_ID,
            $dataLoader,
            \Closure::fromCallable([$this, 'initConfigData']),
            \Closure::fromCallable([$this, 'cacheConfigData'])
        );
    }

    /**
     * @param $data
     * @param $pathKeys
     * @return array|string|int|mixed|null
     */
    private function getDataByPathKeys($data, $pathKeys)
    {
        foreach ($pathKeys as $key) {
            if ((array) $data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof DataObject) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    private function initConfigData(): array
    {
        $data = [];
        foreach ($this->getConfigData->execute($this->getProfileId()) as $item) {
            if (!isset(
                $item[ConfigInterface::SCOPE],
                $item[ConfigInterface::SCOPE_ID],
                $item[ConfigInterface::PATH],
                $item[ConfigInterface::VALUE]
            )) {
                continue;
            }

            if ($item[ConfigInterface::SCOPE] !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $code = $this->scopeCodeResolver->resolve(
                    $item[ConfigInterface::SCOPE],
                    $item[ConfigInterface::SCOPE_ID]
                );
                $data[$item[ConfigInterface::SCOPE]][$code][$item[ConfigInterface::PATH]]
                    = $item[ConfigInterface::VALUE];
                continue;
            }

            $data[$item[ConfigInterface::SCOPE]][$item[ConfigInterface::PATH]] = $item[ConfigInterface::VALUE];
        }

        foreach ($data as $scope => $items) {
            if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $data[$scope] = $this->converter->convert($items);
            } else {
                foreach ($items as $scopeCode => $item) {
                    $data[$scope][$scopeCode] = $this->converter->convert($item);
                }
            }
        }

        return $this->fallback->process($data);
    }

    /**
     * @param array $data
     * @return void
     */
    private function cacheConfigData(array $data): void
    {
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data)),
            $this->getCacheId(),
            [self::CACHE_TAG]
        );

        if (isset($data['default'])) {
            $this->cache->save(
                $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data['default'])),
                $this->getCacheId() . '_default',
                [self::CACHE_TAG]
            );
        }

        $scopes = [];
        foreach ([StoreScopeInterface::SCOPE_WEBSITES, StoreScopeInterface::SCOPE_STORES] as $curScopeType) {
            foreach ($data[$curScopeType] ?? [] as $curScopeId => $curScopeData) {
                $scopes[$curScopeType][$curScopeId] = 1;
                $this->cache->save(
                    $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($curScopeData)),
                    $this->getCacheId() . '_' . $curScopeType . '_' . $curScopeId,
                    [self::CACHE_TAG]
                );
            }
        }

        if (!empty($scopes)) {
            $this->cache->save(
                $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($scopes)),
                $this->getCacheId() . '_scopes',
                [self::CACHE_TAG]
            );
        }
    }

    /**
     * @param string $scope
     * @param string|null $path
     * @param null $scopeCode
     * @return string
     */
    private function generatePath(
        string $scope,
        ?string $path = null,
        $scopeCode = null
    ): string {
        $scope === StoreScopeInterface::SCOPE_STORE
            ? $scope = StoreScopeInterface::SCOPE_STORES
            : ($scope === StoreScopeInterface::SCOPE_WEBSITE ? $scope = StoreScopeInterface::SCOPE_WEBSITES : null);

        $configPath = $scope;
        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            if ((null === $scopeCode || \is_numeric($scopeCode))
                && $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode)
            ) {
                $configPath .= '/' . $scopeCode;
            } elseif ($scopeCode instanceof ScopeInterface && $scopeCode->getCode()) {
                $configPath .= '/' . $scopeCode->getCode();
            }
        }

        if (null !== $path) {
            $configPath .= '/' . $path;
        }

        return $configPath;
    }

    /**
     * @return int|null
     */
    private function getProfileId(): ?int
    {
        return $this->profileId;
    }

    /**
     * @param int $profileId
     * @return $this
     */
    private function setProfileId(int $profileId): ConfigScope
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * @return string
     */
    private function getCacheId(): string
    {
        return self::CONFIG_TYPE . '_' . $this->getProfileId();
    }
}
