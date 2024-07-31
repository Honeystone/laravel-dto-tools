<?php

declare(strict_types=1);

namespace Honeystone\DtoTools;

use Honeystone\DtoTools\Contracts\StorableRelationships;
use Honeystone\DtoTools\Contracts\Transferable;
use Honeystone\DtoTools\Exceptions\RelationNotFoundException;
use Honeystone\DtoTools\Exceptions\RelationValidationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

use function array_key_exists;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function str_replace;

final class Relationships implements StorableRelationships
{
    /**
     * @var array<string, int|string|null>
     */
    private array $one = [];

    /**
     * @var array<string, array<int|string>|null>
     */
    private array $many = [];

    /**
     * @var array<string, array<int|string>>
     */
    private array $additions = [];

    /**
     * @var array<string, array<int|string>>
     */
    private array $deletions = [];

    /**
     * @var array<string, array<int|string, array<string, mixed>>>
     */
    private array $meta = [];

    /**
     * @var array<string, array<string>>
     */
    private array $toOne;

    /**
     * @var array<string, array<string>>
     */
    private array $toMany;

    /**
     * @param array<int|string, string> $toOne
     * @param array<int|string, string> $toMany
     */
    public function __construct(array $toOne, array $toMany)
    {
        $this->unpackRelationships($toOne, $toMany);
    }

    public function __get(string $name): mixed
    {
        if ($this->isToOneRelationship($name)) {
            return $this->getOneRelated($name);
        }

        return $this->getManyRelated($name);
    }

    public function __set(string $name, mixed $value): void
    {
        if ($this->isToOneRelationship($name)) {
            $this->setOneRelated($name, $value);
            return;
        }

        $this->replaceToMany($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->hasToOne($name) || $this->hasToMany($name);
    }

    public function hasToOne(string $relationship): bool
    {
        return array_key_exists($relationship, $this->one);
    }

    public function getOneRelated(string $relationship): int|string|null
    {
        $this->checkToOneRelationshipExists($relationship);

        return $this->one[$relationship] ?? null;
    }

    public function hasToMany(string $relationship): bool
    {
        return array_key_exists($relationship, $this->many);
    }

    public function getManyRelated(string $relationship): ?array
    {
        $this->checkToManyRelationshipExists($relationship);

        if (!array_key_exists($relationship, $this->many) || $this->many[$relationship] === null) {
            return in_array('null', $this->getRelationshipTypes($relationship), true) ? null : [];
        }

        return (new Collection([
            ...$this->many[$relationship],
            ...$this->getManyAdditions($relationship),
        ]))
            ->diff($this->getManyRemovals($relationship))
            ->values()
            ->toArray();
    }

    public function getManyAdditions(string $relationship): array
    {
        $this->checkToManyRelationshipExists($relationship);

        return $this->additions[$relationship] ?? [];
    }

    public function getManyRemovals(string $relationship): array
    {
        $this->checkToManyRelationshipExists($relationship);

        return $this->deletions[$relationship] ?? [];
    }

    public function setOneRelated(string $relationship, int|string|Transferable|null $id): void
    {
        $id = $this->transferableToId($id);

        $this->checkToOneRelationship($relationship, $id);

        $this->one[$relationship] = $id;
    }

    public function unsetOneRelated(string $relationship): void
    {
        $this->checkToOneRelationshipExists($relationship);

        unset($this->one[$relationship]);
    }

    public function addToManyRelation(
        string $relationship,
        int|string|Transferable $id,
        array|Transferable|null $meta = null,
    ): void {

        $id = $this->transferableToId($id);
        $meta = $this->transferableToMeta($meta);

        $this->checkToManyRelationship($relationship, $id);

        if (!array_key_exists($relationship, $this->additions)) {
            $this->additions[$relationship] = [];
        }

        if (!array_key_exists($relationship, $this->meta)) {
            $this->meta[$relationship] = [];
        }

        $this->additions[$relationship][] = $id;

        if ($meta !== null) {
            $this->meta[$relationship][$id] = $meta;
        }

        $this->removeFromDeletions($relationship, $id);
    }

    public function removeToManyRelation(string $relationship, int|string|Transferable $id): void
    {
        $id = $this->transferableToId($id);

        $this->checkToManyRelationship($relationship, $id);

        if (!array_key_exists($relationship, $this->deletions)) {
            $this->deletions[$relationship] = [];
        }

        $this->deletions[$relationship][] = $id;

        $this->removeFromAdditions($relationship, $id);
    }

    public function replaceToMany(
        string $relationship,
        ?array $ids = null,
        array|Transferable|null $meta = null,
    ): void {

        $this->checkToManyRelationshipExists($relationship);

        $this->meta[$relationship] = [];

        if ($ids === null) {
            $this->replaceToManyWithNull($relationship);

        } elseif (count($ids) === 0) {
            $this->replaceToManyWithEmpty($relationship);

        } else {
            $finalIds = [];

            foreach ($ids as $id) {
                [$checkId, $meta] = $this->extractMetaDataFromId($id, $meta ?? []);

                $this->checkRelationIdType($relationship, $checkId);

                $finalIds[] = $checkId;
            }

            $this->many[$relationship] = $finalIds;
        }

        $this->meta[$relationship] = $meta ?? [];

        unset($this->additions[$relationship], $this->deletions[$relationship]);
    }

    public function resetToMany(string $relationship): void
    {
        $this->checkToManyRelationshipExists($relationship);

        if (
            !$this->isRelationNullable($relationship) &&
            !$this->canRelationBeEmpty($relationship)
        ) {
            throw new RelationValidationException("The {$relationship} relationship must be set.");
        }

        unset(
            $this->many[$relationship],
            $this->additions[$relationship],
            $this->deletions[$relationship],
            $this->meta[$relationship]
        );
    }

    public function getMetaData(string $relationship): array
    {
        return $this->meta[$relationship] ?? [];
    }

    public function setMetaData(string $relationship, array|Transferable $meta = []): void
    {
        $this->meta[$relationship] = $this->transferableToMeta($meta);
    }

    public function toArray(): array
    {
        [$one, $many] = (new Collection([$this->toOne, $this->toMany]))
            ->map(
                fn (array $relationships): array => (new Collection($relationships))
                    ->map(function (array $types, string $relationship): int|string|array|null {

                        if ($this->isToOneRelationship($relationship)) {
                            return $this->getOneRelated($relationship);
                        }

                        return $this->getManyRelated($relationship);
                    })->toArray(),
            )->toArray();

        return array_merge($one, $many);
    }

    /**
     * Unpack shorthand relationships to allowed types.
     *
     * @param array<int|string, string> $toOne
     * @param array<int|string, string> $toMany
     */
    private function unpackRelationships(array $toOne, array $toMany): void
    {
        [$this->toOne, $this->toMany] = (new Collection([$toOne, $toMany]))
            ->map(
                static fn (array $relationships): array => (new Collection($relationships))
                    ->mapWithKeys(static function (string $value, int|string $key): array {

                    if (is_string($key)) {
                        return [$key => explode('|', $value)];
                    }

                    return [$value => ['int', 'string', 'empty', 'null']];
                    })->toArray(),
            )->toArray();
    }

    /**
     * Check the relationship being set or removed.
     */
    private function checkToOneRelationship(string $relationship, int|string|null $id): void
    {
        $this->checkToOneRelationshipExists($relationship);
        $this->checkRelationIdType($relationship, $id);
    }

    /**
     * Check the relationship being added or removed.
     */
    private function checkToManyRelationship(string $relationship, int|string $id): void
    {
        $this->checkToManyRelationshipExists($relationship);
        $this->checkRelationIdType($relationship, $id);
    }

    /**
     * Check the one relationship exits on this DTO.
     */
    private function checkToOneRelationshipExists(string $relationship): void
    {
        if (!array_key_exists($relationship, $this->toOne)) {
            throw new RelationNotFoundException('To-one relationship `'.$relationship.'` not found.');
        }
    }

    /**
     * Check the many relationship exits on this DTO.
     */
    private function checkToManyRelationshipExists(string $relationship): void
    {
        if (!array_key_exists($relationship, $this->toMany)) {
            throw new RelationNotFoundException('To-many relationship `'.$relationship.'` not found.');
        }
    }

    private function isRelationNullable(string $relationship): bool
    {
        $expectedTypes = $this->getRelationshipTypes($relationship);

        return in_array('null', $expectedTypes, true);
    }

    private function canRelationBeEmpty(string $relationship): bool
    {
        $expectedTypes = $this->getRelationshipTypes($relationship);

        return in_array('empty', $expectedTypes, true);
    }

    /**
     * Check the specified relation's id type.
     */
    private function checkRelationIdType(string $relationship, int|string|null $id): void
    {
        $expectedTypes = $this->getRelationshipTypes($relationship);
        $givenType = strtolower(gettype($id));

        if (!in_array($givenType, $expectedTypes, true)) {
            throw new RelationValidationException(
                'The related id should be of the type '.
                implode('|', $expectedTypes).", instead the id was `{$id}` ({$givenType}).",
            );
        }
    }

    /**
     * Get the specified relationship's types.
     *
     * @return array<string>
     */
    private function getRelationshipTypes(string $relationship): array
    {
        $types = $this->isToOneRelationship($relationship) ?
            $this->toOne[$relationship] :
            $this->toMany[$relationship];

        return (new Collection($types))->map(
            static function (string $type): string {
                $type = str_replace('[]', '', $type);

                if ($type === 'int') {
                    return 'integer';
                }

                return $type;
            },
        )->toArray();
    }

    /**
     * Check if the specified relationship is a to-one relationship.
     */
    private function isToOneRelationship(string $relationship): bool
    {
        return array_key_exists($relationship, $this->toOne);
    }

    /**
     * Remove an id from the specified relationship's additions.
     */
    private function removeFromAdditions(string $relationship, int|string $id): void
    {
        $additions = $this->getManyAdditions($relationship);

        if (in_array($id, $additions, true)) {
            $this->additions[$relationship] = Arr::except($additions, $id);
        }
    }

    /**
     * Remove an id from the specified relationship's deletions.
     */
    private function removeFromDeletions(string $relationship, int|string $id): void
    {
        $deletions = $this->getManyRemovals($relationship);

        if (in_array($id, $deletions, true)) {
            $this->deletions[$relationship] = Arr::except($deletions, $id);
        }
    }

    private function replaceToManyWithNull(string $relationship): void
    {
        if (!$this->isRelationNullable($relationship)) {
            throw new RelationValidationException("The {$relationship} relationship must not be null.");
        }

        $this->many[$relationship] = null;
    }

    private function replaceToManyWithEmpty(string $relationship): void
    {
        if (!$this->canRelationBeEmpty($relationship)) {

            throw new RelationValidationException("The {$relationship} relationship must not be empty.");
        }

        $this->many[$relationship] = [];
    }

    /**
     * @param int|string|Transferable|array{
     *     id: int|string|Transferable,
     *     pivot?: array<int|string, array<string, mixed>>|null,
     * }|null $id
     *
     * @param array<int|string, array<string, mixed>> $existingPivot
     *
     * @return array{0: int|string, 1: array<int|string, array<string, mixed>>}
     */
    private function extractMetaDataFromId(int|string|Transferable|array|null $id, array $existingPivot): array
    {
        $realId = $id;

        if (is_array($id) && array_key_exists('id', $id)) {
            $realId = $id['id'];
            $existingPivot[$realId] = $id['pivot'] ?? [];
        }

        return [
            $this->transferableToId($realId),
            $this->transferableToMeta($existingPivot),
        ];
    }

    private function transferableToId(int|string|Transferable|null $id): int|string|null
    {
        if ($id instanceof Transferable) {
            return $id->getKey();
        }

        return $id;
    }

    /**
     * @param array<int|string, array<string, mixed>>|Transferable|null $meta
     *
     * @return array<int|string, array<string, mixed>>|null
     */
    private function transferableToMeta(array|Transferable|null $meta): array|null
    {
        if ($meta instanceof Transferable) {
            return $meta->toArray();
        }

        return $meta;
    }
}
