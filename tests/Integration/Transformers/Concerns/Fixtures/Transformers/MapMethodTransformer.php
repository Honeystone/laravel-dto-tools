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
final class MapMethodTransformer extends ModelTransformer
{
    protected string $dataClass = FooData::class;

    /**
     * @param array<string, mixed> $extra
     *
     * @return array<string, mixed>
     */
    protected function map(Model $model, array $extra = []): array
    {
        return $extra + $model->only(
            'name',
            'description',
            'featured',
            'state',
            'modified',
        );
    }
}
