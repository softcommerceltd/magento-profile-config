<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use SoftCommerce\Profile\Model\GetProfileTypeIdInterface;

/**
 * Class AbstractConfig provides global configuration data
 * for profile entities and serves as a main wrapper class.
 */
class AbstractConfig extends DataObject
{
    public const MAGENTO_ATTRIBUTE = 'magento_attribute';
    public const PLENTY_ATTRIBUTE = 'plenty_attribute';

    protected $context;

    /**
     * @var ConfigScopeInterface
     */
    protected $configScope;

    /**
     * @var CollectionFactory
     */
    protected $dataCollectionFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var GetProfileTypeIdInterface
     */
    protected $getProfileTypeId;

    /**
     * @var int|null
     */
    protected $profileId;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var string[]
     */
    private $typeIdInMemory;

    /**
     * @param ConfigScopeInterface $configScope
     * @param CollectionFactory $dataCollectionFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param GetProfileTypeIdInterface $getProfileTypeId
     * @param SerializerInterface $serializer
     * @param array $data
     * @param int|null $profileId
     */
    public function __construct(
        ConfigScopeInterface $configScope,
        CollectionFactory $dataCollectionFactory,
        DataObjectFactory $dataObjectFactory,
        GetProfileTypeIdInterface $getProfileTypeId,
        SerializerInterface $serializer,
        array $data = [],
        ?int $profileId = null
    ) {
        $this->configScope = $configScope;
        $this->dataCollectionFactory = $dataCollectionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->getProfileTypeId = $getProfileTypeId;
        $this->serializer = $serializer;
        $this->profileId = $profileId;
        parent::__construct($data);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getProfileId(): int
    {
        if (!$this->profileId) {
            throw new LocalizedException(__('Profile ID is not set'));
        }
        return $this->profileId;
    }

    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId(int $profileId)
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * @param string $xmlPath
     * @param int|string|null $store [store/website id/code]
     * @param string $scope
     * @return array|mixed|null
     * @throws LocalizedException
     */
    public function getConfig(
        string $xmlPath,
        $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ) {
        $indexKey = $this->getIndexKey($xmlPath, $store, $scope);
        if (!$this->hasData($indexKey)) {
            $this->setData($indexKey, $this->configScope->get($this->getProfileId(), $xmlPath, $scope, $store));
        }

        return $this->getData($indexKey);
    }

    /**
     * @param string $xmlPath
     * @param null $store
     * @param string $scope
     * @return array
     * @throws LocalizedException
     */
    protected function getConfigDataSerialized(
        string $xmlPath,
        $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): array {
        if (!$data = $this->getConfig($xmlPath, $store, $scope)) {
            return [];
        }

        try {
            $data = $this->serializer->unserialize($data);
        } catch (\InvalidArgumentException $e) {
            $data = [];
        }

        return $data;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function getTypeId(): string
    {
        if (!isset($this->typeIdInMemory[$this->getProfileId()])) {
            $this->typeIdInMemory[$this->getProfileId()] = $this->getProfileTypeId->execute($this->getProfileId());
        }
        return $this->typeIdInMemory[$this->getProfileId()];
    }

    /**
     * @param array $dataMap
     * @return array
     */
    protected function generateAttributeDataMap(array $dataMap): array
    {
        $result = [];
        foreach ($dataMap as $item) {
            if (isset($item[self::MAGENTO_ATTRIBUTE], $item[self::PLENTY_ATTRIBUTE])) {
                $result[$item[self::MAGENTO_ATTRIBUTE]] = $item[self::PLENTY_ATTRIBUTE];
            }
        }

        return $result;
    }

    /**
     * @param string $xmlPath
     * @param int|string|null $store
     * @param string $scope
     * @return string
     * @throws LocalizedException
     */
    private function getIndexKey(
        string $xmlPath,
        $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): string {
        $store = $store ?: 0;
        $result = "{$this->getProfileId()}_{$store}_{$scope}_";
        $result .= str_replace('/', '_', trim($xmlPath));
        return $result;
    }
}
