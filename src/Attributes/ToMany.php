<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Attributes;

use Attribute;

#[Attribute]
final readonly class ToMany
{
    /**
     * @param array<string, string> $relations
     */
    public function __construct(public array $relations)
    {
    }
}
