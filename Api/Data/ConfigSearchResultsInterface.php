<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SoftCommerce\ProfileConfig\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @inheritDoc
 */
interface ConfigSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return SearchResultsInterface
     */
    public function setItems(array $items);
}
