<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Contracts;

interface StorableRelationships
{
    /**
     * Check if a to-one relationship has been specified.
     */
    public function hasToOne(string $relationship): bool;

    /**
     * Get the specified to one relationship's relation id.
     */
    public function getOneRelated(string $relationship): int|string|null;

    /**
     * Set the id, or null, to the specified to-one relationship.
     */
    public function setOneRelated(string $relationship, int|string|Transferable|null $id): void;

    /**
     * Unset the specified to-one relationship.
     */
    public function unsetOneRelated(string $relationship): void;

    /**
     * Check if a to-many relationship replacement has been specified.
     */
    public function hasToMany(string $relationship): bool;

    /**
     * Get the specified to-many relationship replacement data, plus any
     * additions, minus any deletions.
     *
     * @return array<int|string>|null
     */
    public function getManyRelated(string $relationship): ?array;

    /**
     * Get the additions to the specified to-many relationship.
     *
     * @return array<int|string>
     */
    public function getManyAdditions(string $relationship): array;

    /**
     * Get the deletions form the specified to-many relationship.
     *
     * @return array<int|string>
     */
    public function getManyRemovals(string $relationship): array;

    /**
     * Add the given id to the specified to-many relationship.
     *
     * @param array<string, mixed>|Transferable|null $meta
     *
     * @see removeToManyRelation()
     */
    public function addToManyRelation(
        string $relationship,
        int|string|Transferable $id,
        array|Transferable|null $meta = null,
    ): void;

    /**
     * Remove the given id from the specified to many relationship.
     *
     * @see addToManyRelation()
     */
    public function removeToManyRelation(string $relationship, int|string|Transferable $id): void;

    /**
     * Replace the specified to-many relationship overriding any existing
     * additions or deletions.
     *
     * @param array<int|string|Transferable|array{
     *     id: int|string|Transferable,
     *     meta?: array<int|string, array<string, mixed>>|null,
     * }>|null $ids
     * @param array<int|string, array<string, mixed>>|Transferable $meta
     */
    public function replaceToMany(
        string $relationship,
        ?array $ids = null,
        array|Transferable $meta = [],
    ): void;

    /**
     * Reset the specified to-many relationship.
     */
    public function resetToMany(string $relationship): void;

    /**
     * @return array<string|int, array<string|mixed>>
     */
    public function getMetaData(string $relationship): array;

    /**
     * @param array<string|int, array<string|mixed>>|Transferable $meta
     */
    public function setMetaData(string $relationship, array|Transferable $meta = []): void;

    /**
     * Get an array of all relationships.
     *
     * @return array<string, int|string|array<int|string>|null>
     */
    public function toArray(): array;
}
