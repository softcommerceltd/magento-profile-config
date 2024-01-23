<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use SoftCommerce\ProfileConfig\Model\ResourceModel;

/**
 * @inheritDoc
 */
class GetProfileConfigValueByPath implements GetProfileConfigValueByPathInterface
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var ResourceModel\Config
     */
    private ResourceModel\Config $resource;

    /**
     * @param ResourceModel\Config $resource
     */
    public function __construct(ResourceModel\Config $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $path): array
    {
        if (!isset($this->data[$path])) {
            $this->data[$path] = $this->resource->getDataByPath($path);
        }

        return $this->data[$path];
    }
}
