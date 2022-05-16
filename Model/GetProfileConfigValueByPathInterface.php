<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface GetProfileConfigValueByPathInterface used to
 * obtain profile config value by XML path.
 */
interface GetProfileConfigValueByPathInterface
{
    /**
     * @param string $path
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $path): array;
}
