<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Casters;

use Attribute;
use Honeystone\DtoTools\Casters\Contracts\CastsValues;

use function in_array;
use function is_array;

#[Attribute]
final readonly class NullCaster implements CastsValues
{
    /**
     * @var array<mixed>
     */
    private array $values;

    /**
     * @param string|array<string> ...$values
     */
    public function __construct(mixed ...$values)
    {
        $this->values = is_array($values[0] ?? null) ? $values[0] : $values;
    }

    public function cast(mixed $value): mixed
    {
        return in_array($value, $this->values, true) ? null : $value;
    }
}
