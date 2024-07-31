<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers;

use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Data\BarData;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Models\Foo;
use Honeystone\DtoTools\Transformers\ModelTransformer;

/**
 * @extends ModelTransformer<Foo, BarData>
 */
final class BarTransformer extends ModelTransformer
{
    protected string $dataClass = BarData::class;
}
