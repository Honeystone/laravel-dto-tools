<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers\Contracts;

interface ManipulatesSchema
{
    /**
     * @return $this
     */
    public function exclude(string ...$attributes): self;

    /**
     * @param array<string, mixed> $attributes
     *
     * @return $this
     */
    public function override(array $attributes): self;
}
