<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Contracts;

interface Storable extends Transferable
{
    /**
     * If patching, null attribute values should not be stored unless forced.
     * If not patching, all attribute values are stored.
     *
     * Patching should be implementation specific. For example, a creation DTO
     * would not be patching, but an update DTO may be.
     *
     * @see force()
     */
    public function isPatching(): bool;

    /**
     * Check if an attribute is storable.
     */
    public function isStorable(string $attribute): bool;

    /**
     * Get forced attributes.
     *
     * @return array<string>
     */
    public function getForced(): array;

    /**
     * @see isPatching()
     *
     * @param string ...$attributes
     */
    public function force(string ...$attributes): static;

    /**
     * Manage storable relationships.
     *
     * @return StorableRelationships
     */
    public function relationships(): StorableRelationships;

    /**
     * Get an array of all relationships.
     *
     * @return array<string, int|string|array<int|string>|null>
     */
    public function getRelationships(): array;

    /**
     * Get an array of only storable attributes.
     *
     * @return array<string, mixed>
     */
    public function toStorableArray(): array;
}
