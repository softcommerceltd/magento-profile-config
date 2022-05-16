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
 * Interface EventConfigInterface used to provide
 * observer event configuration data.
 */
interface EventConfigInterface extends ConfigInterface
{
    public const ENTITY = 'event_config';

    // Even config paths
    public const XML_PATH_NEW_ENTITY_OBSERVER = '/event_config/new_entity_observer';
    public const XML_PATH_DELETE_ENTITY_OBSERVER = '/event_config/deleted_entity_observer';

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function canObserveNewEntity(): bool;

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function canObserveDeletedEntity(): bool;
}
