<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Concerns;

trait HasTransferableData
{
    use CreatableFromArray, SerializesToArray;

    public function getKey(): int|string
    {
        return $this->{$this->keyProperty ?? 'id'};
    }

    public function getAttributes(): array
    {
        return $this->toRawArray();
    }
}
