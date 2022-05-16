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
use SoftCommerce\Profile\Model\Profile;

/**
 * @inheritDoc
 */
class RegistryLocator implements RegistryLocatorInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return ProfileInterface|Profile
     * @throws LocalizedException
     */
    public function getProfile()
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
     * @return StoreInterface|mixed|null
     * @throws LocalizedException
     */
    public function getStore()
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
