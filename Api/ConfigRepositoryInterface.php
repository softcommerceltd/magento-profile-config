<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ConfigRepositoryInterface
 * used to provide profile config entity data.
 */
interface ConfigRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\ConfigSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $entityId
     * @param string|null $field
     * @return Data\ConfigInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId, ?string $field = null);

    /**
     * @param Data\ConfigInterface $config
     * @return Data\ConfigInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\ConfigInterface $config);

    /**
     * @param Data\ConfigInterface $config
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ConfigInterface $config);

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId);
}
