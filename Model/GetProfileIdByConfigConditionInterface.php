<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface GetProfileIdByConfigConditionInterface used to
 * obtain profile ID(s) based on given configuration condition.
 */
interface GetProfileIdByConfigConditionInterface
{
    /**
     * @param string $path
     * @param string|int|mixed $value
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $path, $value = null): array;
}
