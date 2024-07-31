<?php

declare(strict_types=1);

use Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures\CastingData;
use Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures\TransformationData;

it('creates from array')
    ->expect(TransformationData::make(['value' => 'foo'])->value)
    ->toBe('FOO');

it('creates from named args')
    ->expect(TransformationData::make(value: 'foo', data: ['bar'])->getAttributes())
    ->toBe([
        'value' => 'FOO',
        'data' => ['bar'],
    ]);

it('transforms incoming')
    ->expect(TransformationData::make(value: 'foo')->value)
    ->toBe('FOO');

it('casts incoming to string')
    ->expect(CastingData::make(default: 888)->default)
    ->toBe('888');

it('casts incoming to int')
    ->expect(CastingData::make(stringInt: false)->stringInt)
    ->toBe(0);

it('keeps incoming string')
    ->expect(CastingData::make(stringInt: 'foo')->stringInt)
    ->toBe('foo');
