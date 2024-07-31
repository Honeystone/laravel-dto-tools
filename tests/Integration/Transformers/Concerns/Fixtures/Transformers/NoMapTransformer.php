<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers;

use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Data\FooData;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Models\Foo;
use Honeystone\DtoTools\Transformers\ModelTransformer;

/**
 * @extends ModelTransformer<Foo, FooData>
 */
final class NoMapTransformer extends ModelTransformer
{
    protected string $dataClass = FooData::class;
}
