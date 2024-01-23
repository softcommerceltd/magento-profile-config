<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigSearchResultsInterface;
use SoftCommerce\ProfileConfig\Api\Data\ConfigSearchResultsInterfaceFactory;
use SoftCommerce\ProfileConfig\Api\ConfigRepositoryInterface;

/**
 * @inheritDoc
 */
class ConfigRepository implements ConfigRepositoryInterface
{
    /**
     * @var ResourceModel\Config
     */
    private ResourceModel\Config $resource;

    /**
     * @var ConfigFactory
     */
    private ConfigFactory $modelFactory;

    /**
     * @var ResourceModel\Config\CollectionFactory
     */
    private ResourceModel\Config\CollectionFactory $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var ConfigSearchResultsInterfaceFactory
     */
    private ConfigSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @param ResourceModel\Config $resource
     * @param ResourceModel\Config\CollectionFactory $collectionFactory
     * @param ConfigFactory $modelFactory
     * @param ConfigSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel\Config $resource,
        ResourceModel\Config\CollectionFactory $collectionFactory,
        ConfigFactory $modelFactory,
        ConfigSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ConfigSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var ConfigSearchResultsInterface $searchResults */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param int $entityId
     * @param string|null $field
     * @return ConfigInterface|Config
     * @throws NoSuchEntityException
     */
    public function get(int $entityId, ?string $field = null)
    {
        /** @var ConfigInterface|Config $history */
        $history = $this->modelFactory->create();
        $this->resource->load($history, $entityId, $field);
        if (!$history->getId()) {
            throw new NoSuchEntityException(__('The config with ID "%1" doesn\'t exist.', $entityId));
        }

        return $history;
    }

    /**
     * @param ConfigInterface|Config $config
     * @return ConfigInterface
     * @throws CouldNotSaveException
     */
    public function save(ConfigInterface $config)
    {
        try {
            $this->resource->save($config);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $config;
    }

    /**
     * @param ConfigInterface|Config $config
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ConfigInterface $config)
    {
        try {
            $this->resource->delete($config);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
