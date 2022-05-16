<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Interface ConfigScopeInterface
 * used to provide profile scope config data.
 */
interface ConfigScopeInterface
{
    public const CACHE_TAG = 'softcommerce_config_scope';
    public const CONFIG_TYPE = 'softcommerce_profile_config';
    public const LOCK_ID = 'SOFTCOMMERCE_PROFILE_CONFIG';
    public const REQUEST_TYPE_ID = 'type_id';
    public const REQUEST_ID = 'id';

    /**
     * @param int $profileId
     * @param string|null $path
     * @param string $scope
     * @param string|int|null $scopeCode
     * @return array|string|int|mixed|null
     * @throws \Exception
     */
    public function get(
        int $profileId,
        ?string $path = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    );

    /**
     * @return void
     */
    public function clean(): void;
}
