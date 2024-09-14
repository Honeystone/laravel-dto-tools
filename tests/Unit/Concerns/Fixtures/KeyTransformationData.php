<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures;

use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Contracts\Transferable;

final class KeyTransformationData implements Transferable
{
    use HasTransferableData;

    protected string $keyProperty = 'id';

    public function __construct(
        public int $id,
        public string $value = 'Foo',
    ) {
    }
}
