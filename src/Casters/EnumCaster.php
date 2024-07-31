<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Casters;

use Attribute;
use Honeystone\DtoTools\Casters\Contracts\CastsValues;

use function enum_exists;
use function is_array;

#[Attribute]
final readonly class EnumCaster implements CastsValues
{
    /**
     * @var array<class-string>
     */
    private array $types;

    /**
     * @param class-string|array<class-string> ...$types
     */
    public function __construct(string|array ...$types)
    {
        $this->types = is_array($types[0] ?? null) ? $types[0] : $types;
    }

    public function cast(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        foreach ($this->types as $type) {

            if (!enum_exists($type) || $value instanceof $type) {
                continue;
            }

            /** @phpstan-ignore-next-line */
            $enum = $type::tryFrom($value);

            if ($enum !== null) {
                return $enum;
            }
        }

        return $value;
    }
}
