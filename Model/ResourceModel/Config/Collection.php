<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model\ResourceModel\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use SoftCommerce\ProfileConfig\Model\Config;
use SoftCommerce\ProfileConfig\Model\ResourceModel;

/**
 * @inheritDoc
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ConfigInterface::ENTITY_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(Config::class, ResourceModel\Config::class);
    }

    /**
     * Add scope filter to collection
     *
     * @param $scope
     * @param $scopeId
     * @param $section
     * @param null $profileId
     * @return $this
     */
    public function addScopeFilter($scope, $scopeId, $section, $profileId)
    {
        $this->addFieldToFilter(ConfigInterface::PROFILE_ID, (int) $profileId);
        $this->addFieldToFilter(ConfigInterface::SCOPE, $scope);
        $this->addFieldToFilter(ConfigInterface::SCOPE_ID, $scopeId);
        $this->addFieldToFilter(ConfigInterface::PATH, ['like' => $section . '/%']);
        return $this;
    }

    /**
     *  Add path filter
     *
     * @param string $section
     * @return $this
     */
    public function addPathFilter($section)
    {
        $this->addFieldToFilter(ConfigInterface::PATH, ['like' => $section . '/%']);
        return $this;
    }

    /**
     * Add value filter
     *
     * @param int|string $value
     * @return $this
     */
    public function addValueFilter($value)
    {
        $this->addFieldToFilter(ConfigInterface::VALUE, ['like' => $value]);
        return $this;
    }

    /**
     * Add profile filter
     *
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(ConfigInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }
}
