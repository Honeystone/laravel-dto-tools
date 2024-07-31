<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Data;

use Honeystone\DtoTools\Casters\DateTimeCaster;
use Honeystone\DtoTools\Casters\EnumCaster;
use Honeystone\DtoTools\Casters\ScalarCaster;
use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Contracts\Transferable;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Enums\State;
use Illuminate\Support\Collection;

final readonly class FooData implements Transferable
{
    use HasTransferableData;

    /**
     * @param Collection<string, BazData>|null $baz
     */
    public function __construct(
        #[ScalarCaster('int')]
        public int $id,

        public string $name,
        public string $description,

        #[ScalarCaster('bool')]
        public bool $featured,

        #[EnumCaster(State::class)]
        public State $state,

        #[DateTimeCaster]
        public string $modified,

        public ?BarData $bar = null,
        public ?Collection $baz = null,
    ) {
    }
}
