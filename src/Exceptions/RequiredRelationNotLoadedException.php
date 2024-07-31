<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Exceptions;

use RuntimeException;

final class RequiredRelationNotLoadedException extends RuntimeException
{
    public function __construct(string $dataClass, string $modelClass, string $relation)
    {
        parent::__construct(
            "The {$relation} relation must be loaded on {$modelClass} when transforming into {$dataClass}.",
        );
    }
}
