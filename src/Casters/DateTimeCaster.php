<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Casters;

use Attribute;
use Carbon\Carbon;
use Honeystone\DtoTools\Casters\Contracts\CastsValues;

#[Attribute]
final readonly class DateTimeCaster implements CastsValues
{
    public function __construct(private ?string $format = null)
    {
    }

    public function cast(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = Carbon::make($value);
        }

        if (!$value instanceof Carbon) {
            return null;
        }

        return $this->format !== null ?
            $value->format($this->format) :
            $value->toIso8601String();
    }
}
