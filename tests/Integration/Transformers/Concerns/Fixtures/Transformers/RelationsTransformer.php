<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers;

use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Data\FooData;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Models\Foo;
use Honeystone\DtoTools\Transformers\ModelTransformer;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends ModelTransformer<Foo, FooData>
 */
final class RelationsTransformer extends ModelTransformer
{
    protected string $dataClass = FooData::class;

    /**
     * @param array<string, mixed> $barExtra
     * @param array<string, mixed> $bazExtra
     *
     * @return array<string, mixed>
     */
    protected function map(Model $model, array $barExtra = [], array $bazExtra = []): array
    {
        return [
            'bar' => $this->requireRelated('bar', $model, $barExtra),
            'baz' => $this->includeRelated('baz', $model, ...$bazExtra),
        ] + $model->only(
            'name',
            'description',
            'featured',
            'state',
            'modified',
        );
    }
}
