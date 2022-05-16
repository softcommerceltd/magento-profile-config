<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;

/**
 * @inheritDoc
 */
class ConfigScopeWriter implements ConfigScopeWriterInterface
{
    /**
     * @var ConfigScopeInterface
     */
    private $configScope;

    /**
     * @var ResourceModel\Config
     */
    private $resource;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ConfigScopeInterface $configScope
     * @param ResourceModel\Config $resource
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigScopeInterface $configScope,
        ResourceModel\Config $resource,
        SerializerInterface $serializer
    ) {
        $this->configScope = $configScope;
        $this->resource = $resource;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function save(
        int $profileId,
        string $path,
        $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void {
        if (is_array($value)) {
            try {
                $this->serializer->serialize($value);
            } catch (\InvalidArgumentException $e) {
                $value = null;
            }
        }

        $saveRequest = [
            ConfigInterface::PARENT_ID => $profileId,
            ConfigInterface::SCOPE => $scope,
            ConfigInterface::SCOPE_ID => $scopeId,
            ConfigInterface::PATH => $path,
            ConfigInterface::VALUE => $value
        ];

        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getMainTable())
            ->where(ConfigInterface::PARENT_ID . ' = ?', $profileId)
            ->where(ConfigInterface::PATH . ' = ?', $path)
            ->where(ConfigInterface::SCOPE . ' = ?', $scope)
            ->where(ConfigInterface::SCOPE_ID . ' = ?', $scopeId);

        if ($existingItem = $connection->fetchRow($select)) {
            $this->resource->update(
                $saveRequest,
                [ConfigInterface::ENTITY_ID . ' = ?' => $existingItem[ConfigInterface::ENTITY_ID]]
            );
        } else {
            $this->resource->insert($saveRequest);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(
        int $profileId,
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void {
        $this->resource->remove(
            [
                ConfigInterface::PARENT_ID . ' = ?' => $profileId,
                ConfigInterface::PATH . ' = ?' => $path,
                ConfigInterface::SCOPE . ' = ?' => $scope,
                ConfigInterface::SCOPE_ID . ' = ?' => $scopeId
            ]
        );
    }

    /**
     * @return void
     */
    public function clean(): void
    {
        $this->configScope->clean();
    }
}
