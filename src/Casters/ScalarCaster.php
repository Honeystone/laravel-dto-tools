<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Casters;

use Attribute;
use Honeystone\DtoTools\Casters\Contracts\CastsValues;
use UnexpectedValueException;

use function gettype;
use function in_array;
use function is_array;
use function is_scalar;

#[Attribute]
final readonly class ScalarCaster implements CastsValues
{
    /**
     * @var array<string>
     */
    private array $types;

    /**
     * @param string|array<string> ...$types
     */
    public function __construct(string|array ...$types)
    {
        $this->types = is_array($types[0] ?? null) ? $types[0] : $types;
    }

    public function cast(mixed $value): string|int|bool|float|null
    {
        if (!is_scalar($value)) {
            return $value;
        }

        $lastType = null;

        foreach ($this->types as $type) {

            if (!in_array($type, ['string', 'int', 'bool', 'float', 'null'])) {
                throw new UnexpectedValueException(
                    "A valid scalar type was expected, `{$type}` provided.",
                );
            }

            if (gettype($value) === $type) {
                return $value;
            }

            $lastType = $type;
        }

        return match ($lastType) {
            'string' => (string) $value,
            'int' => (int) $value,
            'bool' => (bool) $value,
            'float' => (float) $value,
            default => null,
        };
    }
}
