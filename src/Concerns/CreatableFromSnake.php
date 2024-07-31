<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Concerns;

use Illuminate\Support\Str;

use function collect;

trait CreatableFromSnake
{
    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    protected static function transformIncoming(array $parameters): array
    {
        return collect($parameters)->mapWithKeys(
            static fn (mixed $value, string $key): array => [Str::camel($key) => $value],
        )->all();
    }
}
