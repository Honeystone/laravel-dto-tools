<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Enums;

enum State: string {
    case DRAFT = 'draft';

    case PUBLISHED = 'published';
}
