<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\ProfileConfig\Api\Data;

/**
 * Interface ConfigInterface
 * used to provide profile config interface layer.
 */
interface ConfigInterface
{
    const DB_TABLE_NAME = 'softcommerce_profile_config';

    const ENTITY_ID = 'entity_id';
    const PARENT_ID = 'parent_id';
    const SCOPE = 'scope';
    const SCOPE_ID = 'scope_id';
    const PATH = 'path';
    const VALUE = 'value';

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @return int|null
     */
    public function getParentId(): ?int;

    /**
     * @param int $parentId
     * @return $this
     */
    public function setParentId(int $parentId);

    /**
     * @return string|null
     */
    public function getScope(): ?string;

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope(string $scope);

    /**
     * @return int|null
     */
    public function getScopeId(): ?int;

    /**
     * @param int $scopeId
     * @return $this
     */
    public function setScopeId(int $scopeId);

    /**
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path);

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|int|mixed $value
     * @return $this
     */
    public function setValue($value);
}
