<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures;

use Honeystone\DtoTools\Concerns\CreatableFromSnake;
use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Concerns\SerializesToSnake;
use Honeystone\DtoTools\Contracts\Transferable;

final readonly class SnakeTransformationData implements Transferable
{
    use CreatableFromSnake, HasTransferableData, SerializesToSnake;

    private function __construct(public string $multiWordParameter) {}
}
