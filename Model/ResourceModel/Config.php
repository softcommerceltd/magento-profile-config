<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Core\Model\ResourceModel\AbstractResource;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;

/**
 * @inheritDoc
 */
class Config extends AbstractResource
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ConfigInterface::DB_TABLE_NAME, ConfigInterface::ENTITY_ID);
    }

    /**
     * @param $profileId
     * @param $path
     * @param string|array $cols
     * @return array
     * @throws LocalizedException
     */
    public function getSearchProfileByEntityTypePath($profileId, $path, $cols = '*')
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), $cols)
            ->where(ConfigInterface::PARENT_ID . ' = ?', $profileId)
            ->where(ConfigInterface::PATH . ' = ?', $path);

        return $adapter->fetchRow($select);
    }

    /**
     * @param string|array $path
     * @param string|array $cols
     * @return array
     * @throws LocalizedException
     */
    public function getSearchConfigByPathAlike($path, $cols = '*')
    {
        if (!is_array($path)) {
            $path = [$path];
        }

        $likeCondition = [];
        foreach ($path as $item) {
            $likeCondition[] = $this->getConnection()->quoteInto('path LIKE ?', "$item%");
        }
        $likeCondition = implode(' OR ', $likeCondition);

        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), $cols)
            ->where(new \Zend_Db_Expr($likeCondition));

        return $adapter->fetchCol($select);
    }

    /**
     * @param string $path
     * @param string|array $cols
     * @return array
     * @throws LocalizedException
     */
    public function getDataByPath(string $path, $cols = '*'): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), $cols)
            ->where(ConfigInterface::PATH . ' = ?', $path);

        return $connection->fetchAll($select);
    }

    /**
     * @param int $profileId
     * @param string $scopeCode
     * @param $scopeId
     * @throws LocalizedException
     */
    public function clearScopeData(int $profileId, string $scopeCode, $scopeId)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [
                ConfigInterface::PARENT_ID . ' = ?' => $profileId,
                ConfigInterface::SCOPE . ' = ?' => $scopeCode,
                ConfigInterface::SCOPE_ID . ' = ?' => $scopeId
            ]
        );
    }
}
