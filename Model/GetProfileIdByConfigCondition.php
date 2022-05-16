<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use SoftCommerce\ProfileConfig\Api\Data\ConfigInterface;
use SoftCommerce\ProfileConfig\Model\ResourceModel;

/**
 * @inheritDoc
 */
class GetProfileIdByConfigCondition implements GetProfileIdByConfigConditionInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var ResourceModel\Config
     */
    private $resource;

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
    public function execute(string $path, $value = null): array
    {
        if (isset($this->data[$path])) {
            return $this->data[$path] ?? [];
        }

        $data = $this->resource->getDataByPath($path, [ConfigInterface::PARENT_ID, ConfigInterface::VALUE]);
        if (null !== $value) {
            $data = array_filter($data, function ($item) use ($value) {
                return isset($item[ConfigInterface::VALUE]) && $item[ConfigInterface::VALUE] == $value;
            });
        }
        $this->data[$path] = array_column($data, ConfigInterface::PARENT_ID);

        return $this->data[$path];
    }
}
