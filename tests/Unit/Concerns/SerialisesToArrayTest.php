<?php

declare(strict_types=1);

use Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures\TransformationData;

it('transforms arrayables')
    ->expect(TransformationData::make(data: TransformationData::make(value: 'foo'))->toArray())
    ->toBe([
        'transformed' => '',
        'data' => [
            'transformed' => 'FOO',
            'data' => null,
        ],
    ]);

it('transforms nested arrayables')
    ->expect(TransformationData::make(data: [
        'foo' => [
            'bar' =>
                TransformationData::make(value: 'baz'),
        ],
    ],
    )->toArray())
    ->toBe([
        'transformed' => '',
        'data' => [
            'foo' => [
                'bar' => [
                    'transformed' => 'BAZ',
                    'data' => null,
                ]
            ]
        ],
    ]);

it('transforms outgoing')
    ->expect(TransformationData::make(value: 'bar')->toArray())
    ->toBe([
        'transformed' => 'BAR',
        'data' => null,
    ]);

it('allows transformations to be bypassed')
    ->expect(TransformationData::make(value: 'bar')->toRawArray())
    ->toBe([
        'value' => 'BAR',
        'data' => null,
    ]);
