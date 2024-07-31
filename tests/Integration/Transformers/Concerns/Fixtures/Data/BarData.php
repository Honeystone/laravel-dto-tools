<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Data;

use Honeystone\DtoTools\Casters\ScalarCaster;
use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Contracts\Transferable;

final readonly class BarData implements Transferable
{
    use HasTransferableData;

    public function __construct(
        #[ScalarCaster('int')]
        public int $id,
        public ?string $foobar = null,
    ) {
    }
}
