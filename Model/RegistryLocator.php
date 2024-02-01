<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use SoftCommerce\Profile\Api\Data\ProfileInterface;

/**
 * @inheritDoc
 */
class RegistryLocator implements RegistryLocatorInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ProfileInterface|null
     */
    private ?ProfileInterface $profile = null;

    /**
     * @var StoreInterface|null
     */
    private ?StoreInterface $store = null;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return ProfileInterface
     * @throws LocalizedException
     */
    public function getProfile(): ProfileInterface
    {
        if (null !== $this->profile) {
            return $this->profile;
        }

        if ($profile = $this->registry->registry(self::CURRENT_PROFILE)) {
            return $this->profile = $profile;
        }

        throw new LocalizedException(__("The profile wasn't registered."));
    }

    /**
     * @return StoreInterface
     * @throws LocalizedException
     */
    public function getStore(): StoreInterface
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if ($store = $this->registry->registry('current_store')) {
            return $this->store = $store;
        }

        throw new LocalizedException(__("The store wasn't registered. Verify the store and try again."));
    }
}
