<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Event\ManagerInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;

/**
 * @inheritDoc
 */
class Config extends AbstractModel implements ConfigInterface, IdentityInterface
{
    private const CACHE_TAG = 'plenty_profile_config_model';

    /**
     * @var string
     */
    protected string $cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var array|null
     */
    private ?array $configData = null;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Config::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [$this->_eventPrefix . '_' . $this->getEntityId()];
    }

    /**
     * @inheritDoc
     */
    public function getEntityId(): int
    {
        return (int) $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): ?int
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setParentId(int $parentId): static
    {
        $this->setData(self::PARENT_ID, $parentId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getScope(): ?string
    {
        return $this->getData(self::SCOPE);
    }

    /**
     * @inheritDoc
     */
    public function setScope(string $scope): static
    {
        $this->setData(self::SCOPE, $scope);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getScopeId(): ?int
    {
        return $this->getData(self::SCOPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setScopeId(int $scopeId): static
    {
        $this->setData(self::SCOPE_ID, $scopeId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): ?string
    {
        return $this->getData(self::PATH);
    }

    /**
     * @inheritDoc
     */
    public function setPath(string $path): static
    {
        $this->setData(self::PATH, $path);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?string
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value): static
    {
        $this->setData(self::VALUE, $value);
        return $this;
    }
}
