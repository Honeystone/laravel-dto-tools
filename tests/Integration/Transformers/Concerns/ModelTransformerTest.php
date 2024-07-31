<?php

declare(strict_types=1);

use Carbon\Carbon;
use Honeystone\DtoTools\Exceptions\RequiredRelationNotLoadedException;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Data\FooData;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Enums\State;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Models\Bar;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Models\Baz;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Models\Foo;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers\BarTransformer;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers\BazTransformer;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers\MapMethodTransformer;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers\NoMapTransformer;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers\OnlyPropertyTransformer;
use Honeystone\DtoTools\Tests\Integration\Transformers\Concerns\Fixtures\Transformers\RelationsTransformer;
use Honeystone\DtoTools\Transformers\TransformerFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

it('transforms using map method', function (): void {

    $modified = Carbon::now();

    $model = new Foo([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,
        'foobar' => 'baz',
    ]);

    $factory = new TransformerFactory([Foo::class => MapMethodTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),
            'bar' => null,
            'baz' => null,
        ]);
});

it('transforms using only property', function (): void {

    $modified = Carbon::now();

    $model = new Foo([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,
        'foobar' => 'baz',
    ]);

    $factory = new TransformerFactory([Foo::class => OnlyPropertyTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),
            'bar' => null,
            'baz' => null,
        ]);
});

it('fails to transform without any map when there are unknown attributes', function (): void {

    $modified = Carbon::now();

    $model = new Foo([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,
        'foobar' => 'baz',
    ]);

    $factory = new TransformerFactory([Foo::class => NoMapTransformer::class]);

    $factory->makeForModel($model)->transform($model);

})->throws(Error::class);

it('transforms a collection', function (): void {

    $modified = Carbon::now();

    $collection = new EloquentCollection([
        new Foo([
            'id' => '1',
            'name' => 'foo',
            'description' => 'bar',
            'featured' => 1,
            'state' => 'draft',
            'modified' => $modified,
            'foobar' => 'baz',
        ]),
        new Foo([
            'id' => '2',
            'name' => 'foo',
            'description' => 'bar',
            'featured' => 1,
            'state' => 'published',
            'modified' => $modified,
            'foobar' => 'baz',
        ])
    ]);

    $factory = new TransformerFactory([Foo::class => MapMethodTransformer::class]);

    $data = $factory->makeForCollection($collection)->transformCollection($collection);

    expect($data)->toBeInstanceOf(Collection::class)
        ->and($data->first()->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),
            'bar' => null,
            'baz' => null,
        ])
        ->and($data->last()->toArray())->tobe([
            'id' => 2,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::PUBLISHED,
            'modified' => $modified->toIso8601String(),
            'bar' => null,
            'baz' => null,
        ]);
});

it('transforms with exclusions', function (): void {

    $modified = Carbon::now();

    $model = new Foo([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,
        'foobar' => 'baz',
    ]);

    $factory = new TransformerFactory([Foo::class => NoMapTransformer::class]);

    $data = $factory->makeForModel($model)->exclude('foobar')->transform($model);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),
            'bar' => null,
            'baz' => null,
        ]);
});

it('transforms with overrides', function (): void {

    $modified = Carbon::now();

    $model = new Foo([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,
    ]);

    $factory = new TransformerFactory([Foo::class => NoMapTransformer::class]);

    $data = $factory->makeForModel($model)->override(['name' => 'foobar'])->transform($model);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foobar',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),
            'bar' => null,
            'baz' => null,
        ]);
});

it('transforms with parameters', function (): void {

    $modified = Carbon::now();

    $model = new Foo([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
    ]);

    $factory = new TransformerFactory([Foo::class => MapMethodTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model, extra: ['modified' => $modified]);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),
            'bar' => null,
            'baz' => null,
        ]);
});

it('transforms with relations, inlcluded not loaded', function (): void {

    app()->config->set('honeystone-dto-tools.transformers', [
        Bar::class => NoMapTransformer::class,
        Baz::class => NoMapTransformer::class,
    ]);

    $modified = Carbon::now();

    $model = $this->partialMock(Foo::class, function (MockInterface $mock): void {
        $mock->shouldReceive('relationLoaded')->with('bar')->twice()->andReturn(true);
        $mock->shouldReceive('relationLoaded')->with('baz')->once()->andReturn(false);
    });

    $model->fill([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,

        //relations
        'bar' => null,
    ]);

    $factory = new TransformerFactory([$model::class => RelationsTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),

            //relations
            'bar' => null,
            'baz' => null,
        ]);
});

it('transforms with relations, required not loaded', function (): void {

    $model = $this->partialMock(Foo::class, function (MockInterface $mock): void {
        $mock->shouldReceive('relationLoaded')->with('bar')->once()->andReturn(false);
    });

    $model->fill(['id' => '1']);

    $factory = new TransformerFactory([$model::class => RelationsTransformer::class]);

    $factory->makeForModel($model)->transform($model);

})->throws(RequiredRelationNotLoadedException::class);

it('transforms with relations, loaded', function (): void {

    app()->config->set('honeystone-dto-tools.transformers', [
        Bar::class => BarTransformer::class,
        Baz::class => BazTransformer::class,
    ]);

    $modified = Carbon::now();

    $model = $this->partialMock(Foo::class, function (MockInterface $mock): void {
        $mock->shouldReceive('relationLoaded')->with('bar')->twice()->andReturn(true);
        $mock->shouldReceive('relationLoaded')->with('baz')->once()->andReturn(true);
    });

    $model->fill([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,

        //relations
        'bar' => new Bar(['id' => 1]),
        'baz' => new EloquentCollection([new Baz(['id' => 1]), new Baz(['id' => 2])]),
    ]);

    $factory = new TransformerFactory([$model::class => RelationsTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),

            //relations
            'bar' => [
                'id' => 1,
                'foobar' => null,
            ],
            'baz' => [['id' => 1], ['id' => 2]],
        ]);
});

it('transforms with loaded relations and a callback', function (): void {

    app()->config->set('honeystone-dto-tools.transformers', [
        Bar::class => BarTransformer::class,
        Baz::class => BazTransformer::class,
    ]);

    $modified = Carbon::now();

    $model = $this->partialMock(Foo::class, function (MockInterface $mock): void {
        $mock->shouldReceive('relationLoaded')->with('bar')->twice()->andReturn(true);
        $mock->shouldReceive('relationLoaded')->with('baz')->once()->andReturn(true);
    });

    $model->fill([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,

        //relations
        'bar' => new Bar(['id' => 1]),
        'baz' => new EloquentCollection([new Baz(['id' => 1]), new Baz(['id' => 2])]),
    ]);

    $factory = new TransformerFactory([$model::class => RelationsTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model, barExtra: ['callback' => function (Bar $model): void {
        $model->foobar = 'barbaz';
    }]);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),

            //relations
            'bar' => [
                'id' => 1,
                'foobar' => 'barbaz',
            ],
            'baz' => [['id' => 1], ['id' => 2]],
        ]);
});

it('transforms with loaded relations and exclusions', function (): void {

    app()->config->set('honeystone-dto-tools.transformers', [
        Bar::class => BarTransformer::class,
        Baz::class => BazTransformer::class,
    ]);

    $modified = Carbon::now();

    $model = $this->partialMock(Foo::class, function (MockInterface $mock): void {
        $mock->shouldReceive('relationLoaded')->with('bar')->twice()->andReturn(true);
        $mock->shouldReceive('relationLoaded')->with('baz')->once()->andReturn(true);
    });

    $model->fill([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,

        //relations
        'bar' => new Bar(['id' => 1, 'foobar' => 'barbaz']),
        'baz' => new EloquentCollection([new Baz(['id' => 1]), new Baz(['id' => 2])]),
    ]);

    $factory = new TransformerFactory([$model::class => RelationsTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model, barExtra: ['exclude' => ['foobar']]);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),

            //relations
            'bar' => [
                'id' => 1,
                'foobar' => null,
            ],
            'baz' => [['id' => 1], ['id' => 2]],
        ]);
});

it('transforms with loaded relations and overrides', function (): void {

    app()->config->set('honeystone-dto-tools.transformers', [
        Bar::class => BarTransformer::class,
        Baz::class => BazTransformer::class,
    ]);

    $modified = Carbon::now();

    $model = $this->partialMock(Foo::class, function (MockInterface $mock): void {
        $mock->shouldReceive('relationLoaded')->with('bar')->twice()->andReturn(true);
        $mock->shouldReceive('relationLoaded')->with('baz')->once()->andReturn(true);
    });

    $model->fill([
        'id' => '1',
        'name' => 'foo',
        'description' => 'bar',
        'featured' => 1,
        'state' => 'draft',
        'modified' => $modified,

        //relations
        'bar' => new Bar(['id' => 1, 'foobar' => 'barbaz']),
        'baz' => new EloquentCollection([new Baz(['id' => 1]), new Baz(['id' => 2])]),
    ]);

    $factory = new TransformerFactory([$model::class => RelationsTransformer::class]);

    $data = $factory->makeForModel($model)->transform($model, barExtra: ['override' => ['foobar' => 'bar']]);

    expect($data)->toBeInstanceOf(FooData::class)
        ->and($data->toArray())->tobe([
            'id' => 1,
            'name' => 'foo',
            'description' => 'bar',
            'featured' => true,
            'state' => State::DRAFT,
            'modified' => $modified->toIso8601String(),

            //relations
            'bar' => [
                'id' => 1,
                'foobar' => 'bar',
            ],
            'baz' => [['id' => 1], ['id' => 2]],
        ]);
});
