<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Casters\Contracts;

interface CastsValues
{
    public function cast(mixed $value): mixed;
}
