<?php

declare(strict_types=1);

use Honeystone\DtoTools\Contracts\StorableRelationships;
use Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures\CreationData;
use Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures\PatchData;

it('retrieves the relationships instance')
    ->expect(PatchData::make()->relationships())
    ->toBeInstanceOf(StorableRelationships::class);

it('supports creation instances')
    ->expect(CreationData::make()->isPatching())
    ->toBeFalse();

it('supports patching instances')
    ->expect(PatchData::make()->isPatching())
    ->toBeTrue();

it('reports all attributes as storable in creation mode', function (): void {

    $data = CreationData::make(foo: ':-)');

    expect($data->isStorable('foo'))->toBeTrue()
        ->and($data->isStorable('bar'))->toBeTrue()
        ->and($data->isStorable('baz'))->toBeTrue();
});

it('reports all relationships as storable in creation mode', function (): void {

    $data = CreationData::make();

    expect($data->isStorable('parent'))->toBeTrue()
        ->and($data->isStorable('children'))->toBeTrue();
});

it('reports non-null attributes as storable in patch mode', function (): void {

    $data = PatchData::make(foo: ':-)');

    expect($data->isStorable('foo'))->toBeTrue()
        ->and($data->isStorable('bar'))->toBeFalse()
        ->and($data->isStorable('baz'))->toBeTrue();
});

it('reports non-null relationships as storable in patch mode', function (): void {

    $data = PatchData::make();

    $data->relationships()->setOneRelated('parent', 10);

    expect($data->isStorable('parent'))->toBeTrue()
        ->and($data->isStorable('children'))->toBeFalse();
});

it('reports forced null attributes as storable in patch mode', function (): void {

    $data = PatchData::make(foo: ':-)');

    $data->force('bar');

    expect($data->isStorable('foo'))->toBeTrue()
        ->and($data->isStorable('bar'))->toBeTrue()
        ->and($data->isStorable('baz'))->toBeTrue();
});

it('reports forced null relationships as storable in patch mode', function (): void {

    $data = PatchData::make();

    $data->relationships()->setOneRelated('parent', 10);

    $data->force('children');

    expect($data->isStorable('parent'))->toBeTrue()
        ->and($data->isStorable('children'))->toBeTrue();
});

it('outputs serialised storable patch data', function (): void {

    $data = PatchData::make(foo: ':-)');

    $data->relationships()->setOneRelated('parent', 10);
    $data->relationships()->replaceToMany('children', [20, 25, 30]);

    expect($data->toStorableArray())
        ->toBe([
            'foo' => ':-)',
            'baz' => [],
            'parent' => 10,
            'children' => [20, 25, 30],
        ]);
});

it('outputs serialised storable creation data', function (): void {

    $data = CreationData::make(foo: ':-)');

    $data->relationships()->setOneRelated('parent', 10);
    $data->relationships()->replaceToMany('children', [20, 25, 30]);

    expect($data->toStorableArray())
        ->toBe([
            'foo' => ':-)',
            'bar' => null,
            'baz' => [],
            'parent' => 10,
            'children' => [20, 25, 30],
        ]);
});

it('includes forced null values in serialised storable patch data', function (): void {

    $data = PatchData::make(foo: ':-)');

    $data->relationships()->setOneRelated('parent', 10);
    $data->relationships()->replaceToMany('children', [20, 25, 30]);

    $data->force('bar');

    expect($data->toStorableArray())
        ->toBe([
            'foo' => ':-)',
            'bar' => null,
            'baz' => [],
            'parent' => 10,
            'children' => [20, 25, 30],
        ]);
});

it('retrieves serialised relationships', function (): void {

    $data = PatchData::make();

    $data->relationships()->setOneRelated('parent', 10);
    $data->relationships()->replaceToMany('children', [20, 25, 30]);

    expect($data->getRelationships())
        ->toBe(['parent' => 10, 'children' => [20, 25, 30]]);
});
