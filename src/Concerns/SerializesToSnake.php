<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Concerns;

use Illuminate\Support\Str;

use function collect;

trait SerializesToSnake
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function transformOutgoing(array $data): array
    {
        return collect($data)->mapWithKeys(
            static fn (mixed $value, string $key): array => [Str::snake($key) => $value],
        )->toArray();
    }
}
