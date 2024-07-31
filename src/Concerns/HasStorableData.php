<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Concerns;

use Honeystone\DtoTools\Attributes\Patch;
use ReflectionClass;

use function collect;
use function in_array;

trait HasStorableData
{
    use HasTransferableData, HasStorableRelationships;

    /**
     * @var array<string>
     */
    private array $forced = [];

    public function isPatching(): bool
    {
        return count((new ReflectionClass($this))->getAttributes(Patch::class)) > 0;
    }

    final public function isStorable(string $attribute): bool
    {
        if (!$this->isPatching()) {
            return true;
        }

        return isset($this->$attribute) ||
            isset($this->relationships()->$attribute) ||
            in_array($attribute, $this->getForced(), true);
    }

    final public function getForced(): array
    {
        return $this->forced;
    }

    final public function force(string ...$attributes): static
    {
        $this->forced = $attributes;

        return $this;
    }

    public function toStorableArray(): array
    {
        $raw = $this->toRawArray();

        if ($this->isPatching()) {

            $raw = collect($raw)
                ->reject(fn (mixed $value, string $attribute): bool => !$this->isStorable($attribute))
                ->toArray();
        }

        return $this->processOutgoing($raw + $this->relationships()->toArray());
    }

    /**
     * @return array<string>
     */
    private function excludeFromSerialization(): array
    {
        return ['forced', 'relationships'];
    }
}
