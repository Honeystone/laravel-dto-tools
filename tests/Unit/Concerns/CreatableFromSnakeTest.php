<?php

declare(strict_types=1);

use Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures\SnakeTransformationData;

it('can be created from snake-cased parameters')
    ->expect(SnakeTransformationData::make(multi_word_parameter: 'foo')->multiWordParameter)
    ->toBe('foo');
