<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model\Config;

use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\ProfileConfig\Model\ConfigInterface;

/**
 * Interface LogConfigInterface used to provide
 * logger configuration data.
 */
interface LogConfigInterface extends ConfigInterface
{
    public const ENTITY = 'log_config';

    // Log config paths
    public const XML_PATH_IS_ACTIVE_REQUEST_LOG = '/log_config/is_active_request_log';
    public const XML_PATH_IS_ACTIVE_RESPONSE_LOG = '/log_config/is_active_response_log';

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isActiveRequestLog(): bool;

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isActiveResponseLog(): bool;
}
