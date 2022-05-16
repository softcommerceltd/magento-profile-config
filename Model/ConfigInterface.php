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
 * Interface ConfigInterface used to provide
 * general configuration data of profile configuration.
 */
interface ConfigInterface
{
    /**
     * @param int $profileId
     * @return $this
     */
    public function setProfileId(int $profileId);

    /**
     * @param string $path
     * @param int|string|null $store
     * @param string $scope
     * @return array|mixed|null
     * @throws LocalizedException
     */
    public function getConfig(
        string $path,
        $store = null,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    );
}
