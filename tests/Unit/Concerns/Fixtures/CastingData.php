<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures;

use Honeystone\DtoTools\Casters\ScalarCaster;
use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Contracts\Transferable;

final readonly class CastingData implements Transferable
{
    use HasTransferableData;

    public function __construct(
        #[ScalarCaster('string')]
        public string $default = '',
        #[ScalarCaster('string', 'int')]
        public string|int $stringInt = 0,
    ) {
    }
}
