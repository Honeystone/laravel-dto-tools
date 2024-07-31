<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Contracts;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @extends Arrayable<string, mixed>
 */
interface Transferable extends Arrayable
{
    public static function make(mixed ...$parameters): static;

    public function getKey(): int|string;

    /**
     * Get all attributes.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array;
}
