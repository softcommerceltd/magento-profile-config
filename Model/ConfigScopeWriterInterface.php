<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ConfigScopeWriterInterface used
 * to save / delete configuration data.
 */
interface ConfigScopeWriterInterface
{
    /**
     * @param int $profileId
     * @param string $path
     * @param string|array|mixed $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     * @throws LocalizedException
     */
    public function save(
        int $profileId,
        string $path,
        mixed $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void;

    /**
     * @param int $profileId
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return void
     * @throws LocalizedException
     */
    public function delete(
        int $profileId,
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void;

    /**
     * @return void
     */
    public function clean(): void;
}
