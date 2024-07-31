<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures;

use Honeystone\DtoTools\Attributes\ToMany;
use Honeystone\DtoTools\Attributes\ToOne;
use Honeystone\DtoTools\Concerns\HasStorableData;
use Honeystone\DtoTools\Contracts\Storable;

#[ToOne(['parent' => 'int|null'])]
#[ToMany(['children' => 'int|empty|null'])]
final class CreationData implements Storable
{
    use HasStorableData;

    /**
     * @param array<string> $baz
     */
    public function __construct(
        public readonly ?string $foo = null,
        public readonly ?int $bar = null,
        public readonly array $baz = [],
    ) {
    }
}
