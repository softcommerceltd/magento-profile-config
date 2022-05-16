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
class EventConfig extends AbstractConfig implements EventConfigInterface
{
    /**
     * @inheritDoc
     */
    public function canObserveNewEntity(): bool
    {
        return (bool) $this->getConfig($this->getTypeId() . self::XML_PATH_NEW_ENTITY_OBSERVER);
    }

    /**
     * @inheritDoc
     */
    public function canObserveDeletedEntity(): bool
    {
        return (bool) $this->getConfig($this->getTypeId() . self::XML_PATH_DELETE_ENTITY_OBSERVER);
    }
}
