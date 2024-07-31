<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Unit\Fixtures;

use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Contracts\Transferable;

final readonly class FooData implements Transferable
{
    use HasTransferableData;

    public function __construct(public int|string $id)
    {
    }
}
