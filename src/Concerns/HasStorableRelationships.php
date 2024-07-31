<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Concerns;

use Honeystone\DtoTools\Attributes\ToMany;
use Honeystone\DtoTools\Attributes\ToOne;
use Honeystone\DtoTools\Contracts\StorableRelationships;
use Honeystone\DtoTools\Relationships;
use ReflectionClass;

trait HasStorableRelationships
{
    private ?StorableRelationships $relationships = null;

    final public function relationships(): StorableRelationships
    {
        if ($this->relationships === null) {

            $this->relationships = new Relationships(
                $this->getAttributeArg(ToOne::class),
                $this->getAttributeArg(ToMany::class),
            );
        }

        return $this->relationships;
    }

    public function getRelationships(): array
    {
        return $this->relationships()->toArray();
    }

    /**
     * @param class-string<ToOne|ToMany> $class
     *
     * @return array<string, string>
     */
    private function getAttributeArg(string $class): array
    {
        $attributes = (new ReflectionClass($this))->getAttributes($class);

        return count($attributes) > 0 ? $attributes[0]->getArguments()[0] : [];
    }
}
