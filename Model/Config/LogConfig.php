<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model\Config;

use SoftCommerce\ProfileConfig\Model\AbstractConfig;

/**
 * @inheritDoc
 */
class LogConfig extends AbstractConfig implements LogConfigInterface
{
    /**
     * @inheritDoc
     */
    public function isActiveRequestLog(): bool
    {
        return (bool) $this->getConfig($this->getTypeId() . self::XML_PATH_IS_ACTIVE_REQUEST_LOG);
    }

    /**
     * @inheritDoc
     */
    public function isActiveResponseLog(): bool
    {
        return (bool) $this->getConfig($this->getTypeId() . self::XML_PATH_IS_ACTIVE_RESPONSE_LOG);
    }
}
