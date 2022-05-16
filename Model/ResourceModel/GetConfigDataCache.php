<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model\ResourceModel;

/**
 * @inheritDoc
 */
class GetConfigDataCache implements GetConfigDataInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var GetConfigData
     */
    private $resource;

    /**
     * @param GetConfigData $resource
     */
    public function __construct(GetConfigData $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $parentId): array
    {
        if (!isset($this->cache[$parentId])) {
            $this->cache[$parentId] = $this->resource->execute($parentId);
        }

        return $this->cache[$parentId];
    }
}
