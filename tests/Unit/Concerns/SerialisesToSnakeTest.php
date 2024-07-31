<?php

declare(strict_types=1);

use Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures\SnakeTransformationData;

it('serializes to snake-cased keys')
    ->expect(SnakeTransformationData::make(multiWordParameter: 'foo')->toArray())
    ->toBe(['multi_word_parameter' => 'foo']);
