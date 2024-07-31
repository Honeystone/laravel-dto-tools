<?php

declare(strict_types=1);

use Honeystone\DtoTools\Exceptions\RelationValidationException;
use Honeystone\DtoTools\Relationships;
use Honeystone\DtoTools\Tests\Unit\Fixtures\FooData;
use Honeystone\DtoTools\Tests\Unit\Fixtures\MetaData;

it('constructs with relationship definitions', function (): void {

    expect(new Relationships(['parent' => 'int|null'], ['children' => 'int|empty']))
        ->toBeInstanceOf(Relationships::class);
});

it('constructs without relationship definitions', function (): void {

    expect(new Relationships([], []))->toBeInstanceOf(Relationships::class);
});

it('allows valid to-one transferable type', function (): void {

    $relationships = new Relationships(['foo' => 'int|string|null'], []);
    $relationships->setOneRelated('foo', FooData::make(id: 8));

    expect($relationships->foo)->toBe(8);
});

it('allows valid to-one int type', function (): void {

    $relationships = new Relationships(['foo' => 'int|string|null'], []);
    $relationships->setOneRelated('foo', 8);

    expect($relationships->foo)->toBe(8);
});

it('blocks invalid to-one int type', function (): void {

    $relationships = new Relationships(['foo' => 'string|null'], []);
    $relationships->setOneRelated('foo', 8);

})->throws(
    RelationValidationException::class,
    'The related id should be of the type string|null, instead the id was `8` (integer).',
);

it('allows valid to-one string type', function (): void {

    $relationships = new Relationships(['foo' => 'int|string|null'], []);
    $relationships->setOneRelated('foo', '8');

    expect($relationships->foo)->toBe('8');
});

it('blocks invalid to-one string type', function (): void {

    $relationships = new Relationships(['foo' => 'int|null'], []);
    $relationships->setOneRelated('foo', '8');

})->throws(
    RelationValidationException::class,
    'The related id should be of the type integer|null, instead the id was `8` (string).',
);

it('allows valid to-one null value', function (): void {

    $relationships = new Relationships(['foo' => 'int|string|null'], []);
    $relationships->setOneRelated('foo', null);

    expect($relationships->foo)->toBeNull();
});

it('blocks invalid to-one null value', function (): void {

    $relationships = new Relationships(['foo' => 'int|string'], []);
    $relationships->setOneRelated('foo', null);

})->throws(
    RelationValidationException::class,
    'The related id should be of the type integer|string, instead the id was `` (null).',
);

it('allows valid to-many transferable type', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string|empty']);
    $relationships->replaceToMany('bar', [FooData::make(id: 8)]);

    expect($relationships->bar)->toBe([8]);
});

it('allows valid to-many int type', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string|empty']);
    $relationships->replaceToMany('bar', [8]);

    expect($relationships->bar)->toBe([8]);
});

it('blocks invalid to-many int type', function (): void {

    $relationships = new Relationships([], ['bar' => 'string|empty']);
    $relationships->replaceToMany('bar', [8]);

})->throws(
    RelationValidationException::class,
    'The related id should be of the type string|empty, instead the id was `8` (integer).',
);

it('allows valid to-many string type', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string|empty']);
    $relationships->replaceToMany('bar', ['8']);

    expect($relationships->bar)->toBe(['8']);
});

it('blocks invalid to-many string type', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|empty']);
    $relationships->replaceToMany('bar', ['8']);

})->throws(
    RelationValidationException::class,
    'The related id should be of the type integer|empty, instead the id was `8` (string).',
);

it('allows valid empty to-many', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string|empty']);
    $relationships->replaceToMany('bar', []);

    expect($relationships->bar)->toBe([]);
});

it('blocks invalid empty to-many', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string']);
    $relationships->replaceToMany('bar', []);

})->throws(
    RelationValidationException::class,
    'The bar relationship must not be empty.',
);

it('allows valid empty to-many reset', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string|empty']);
    $relationships->resetToMany('bar');

    expect($relationships->bar)->toBe([])
        ->and($relationships->hasToMany('bar'))->toBeFalse();
});

it('blocks invalid empty to-many reset', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string']);
    $relationships->replaceToMany('bar', []);

})->throws(
    RelationValidationException::class,
    'The bar relationship must not be empty.',
);

it('allows valid to-many null reset', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string|empty|null']);
    $relationships->resetToMany('bar');

    expect($relationships->bar)->toBeNull();
});

it('blocks invalid to-many null reset', function (): void {

    $relationships = new Relationships([], ['bar' => 'int|string']);
    $relationships->resetToMany('bar');

})->throws(
    RelationValidationException::class,
    'The bar relationship must be set.',
);

it('unsets to-one relationships', function (): void {

    $relationships = new Relationships(['foo' => 'int|string|null'], []);
    $relationships->setOneRelated('foo', 8);
    $relationships->unsetOneRelated('foo');

    expect($relationships->foo)->toBeNull();
});

it('retrieves to-one relationship values', function (): void {

    $relationships = new Relationships(['foo' => 'string'], []);
    $relationships->setOneRelated('foo', 'bar');

    expect($relationships->getOneRelated('foo'))->toBe('bar');
});

it('retrieves to-many relationship values', function (): void {

    $relationships = new Relationships([], ['foo' => 'string']);
    $relationships->replaceToMany('foo', ['bar']);

    expect($relationships->getManyRelated('foo'))->toBe(['bar']);
});

it('retrieves to-many relationship null values', function (): void {

    $relationships = new Relationships([], ['foo' => 'string|null']);
    $relationships->replaceToMany('foo', null);

    expect($relationships->getManyRelated('foo'))->toBeNull();
});

it('magically retrieves to-one relationship values', function (): void {

    $relationships = new Relationships(['foo' => 'string'], []);
    $relationships->setOneRelated('foo', 'bar');

    expect($relationships->foo)->toBe('bar');
});

it('magically retrieves to-many relationship values', function (): void {

    $relationships = new Relationships([], ['foo' => 'string']);
    $relationships->replaceToMany('foo', ['bar']);

    expect($relationships->foo)->toBe(['bar']);
});

it('magically sets to-one relationship values', function (): void {

    $relationships = new Relationships(['foo' => 'string'], []);
    $relationships->foo = 'bar';

    expect($relationships->getOneRelated('foo'))->toBe('bar');
});

it('magically replaces to-many relationship values', function (): void {

    $relationships = new Relationships([], ['foo' => 'string']);
    $relationships->foo = ['bar'];

    expect($relationships->getManyRelated('foo'))->toBe(['bar']);
});

it('checks if we have a to-one relationship value', function (): void {

    $relationships = new Relationships(['foo' => 'string', 'bar' => 'string|null', 'baz' => 'string|null'], []);
    $relationships->setOneRelated('foo', ':-)');
    $relationships->setOneRelated('bar', null);

    expect($relationships->hasToOne('foo'))->ToBeTrue()
        ->and($relationships->hasToOne('bar'))->ToBeTrue()
        ->and($relationships->hasToOne('baz'))->ToBeFalse();
});

it('checks if we have a to-many relationship replacement', function (): void {

    $relationships = new Relationships([], [
        'foo' => 'string',
        'bar' => 'string|empty|null',
        'baz' => 'string|empty|null',
        'foobar' => 'string'
    ]);

    $relationships->replaceToMany('foo', [':-)']);
    $relationships->replaceToMany('bar', null);
    $relationships->replaceToMany('baz');

    expect($relationships->hasToMany('foo'))->ToBeTrue()
        ->and($relationships->hasToMany('bar'))->ToBeTrue()
        ->and($relationships->hasToMany('baz'))->ToBeTrue()
        ->and($relationships->hasToMany('foobar'))->ToBeFalse();
});

it('magically checks if we have a to-one relationship value', function (): void {

    $relationships = new Relationships(['foo' => 'string', 'bar' => 'string'], []);
    $relationships->setOneRelated('foo', ':-)');

    expect(isset($relationships->foo))->toBeTrue()
        ->and(isset($relationships->bar))->toBeFalse();
});

it('magically checks if we have a to-many relationship replacement', function (): void {

    $relationships = new Relationships([], ['foo' => 'string', 'bar' => 'string']);
    $relationships->replaceToMany('foo', [':-)']);

    expect(isset($relationships->foo))->toBeTrue()
        ->and(isset($relationships->bar))->toBeFalse();
});

it('returns null if we don\'t have a to-one relationship value', function (): void {

    $relationships = new Relationships(['foo' => 'string'], []);

    expect($relationships->getOneRelated('foo'))->toBeNull();
});

it('returns null if we don\'t have a to-many relationship value', function (): void {

    $relationships = new Relationships([], ['foo' => 'string']);

    expect($relationships->getManyRelated('foo'))->toBe([]);
});

it('merges additions and extracts removals when we retrieve a to-many replacement', function (): void {

    $relationships = new Relationships([], ['foo' => 'string']);
    $relationships->replaceToMany('foo', [':-)', '¯\_(ツ)_/¯', '( ͡° ͜ʖ ͡°)']);
    $relationships->addToManyRelation('foo', FooData::make(id: 'ʕ•ᴥ•ʔ'));
    $relationships->removeToManyRelation('foo', ':-)');

    expect($relationships->getManyRelated('foo'))->toBe(['¯\_(ツ)_/¯', '( ͡° ͜ʖ ͡°)', 'ʕ•ᴥ•ʔ']);
});

it('merges additions and extracts removals when retrieving a to-many replacement', function (): void {

    $relationships = new Relationships([], ['foo' => 'string']);
    $relationships->replaceToMany('foo', [':-)', '¯\_(ツ)_/¯', '( ͡° ͜ʖ ͡°)']);
    $relationships->addToManyRelation('foo', 'ʕ•ᴥ•ʔ');
    $relationships->removeToManyRelation('foo', ':-)');

    expect($relationships->getManyRelated('foo'))->toBe(['¯\_(ツ)_/¯', '( ͡° ͜ʖ ͡°)', 'ʕ•ᴥ•ʔ'])
        ->and($relationships->foo)->toBe(['¯\_(ツ)_/¯', '( ͡° ͜ʖ ͡°)', 'ʕ•ᴥ•ʔ']);
});

it('excludes additions and removals when attempting to get a to-many replacement that has not been set', function (): void {

    $relationships = new Relationships([], ['foo' => 'string']);
    $relationships->addToManyRelation('foo', 'ʕ•ᴥ•ʔ');
    $relationships->removeToManyRelation('foo', FooData::make(id: ':-)'));

    expect($relationships->getManyRelated('foo'))->toBe([])
        ->and($relationships->foo)->toBe([]);
});

it('stores to-many relationship additions', function (): void {

    $relationships = new Relationships([], ['foo' => 'int|string|null']);
    $relationships->addToManyRelation('foo', 8);

    expect($relationships->getManyAdditions('foo'))->toBe([8]);
});

it('stores to-many relationship removals', function (): void {

    $relationships = new Relationships([], ['foo' => 'int|string|null']);
    $relationships->removeToManyRelation('foo', FooData::make(id: 8));

    expect($relationships->getManyRemovals('foo'))->toBe([8]);
});

it('stores to-many relationship addition transferable pivot data', function (): void {

    $relationships = new Relationships([], ['foo' => 'int|string|null']);
    $relationships->addToManyRelation('foo', 8, MetaData::make(['order' => 0, 'title' => 'Foo']));

    expect($relationships->getMetaData('foo'))->toBe([8 => ['order' => 0, 'title' => 'Foo']]);
});

it('stores to-many relationship addition pivot data', function (): void {

    $relationships = new Relationships([], ['foo' => 'int|string|null']);
    $relationships->addToManyRelation('foo', 8, ['order' => 0, 'Title' => 'Foo']);

    expect($relationships->getMetaData('foo'))->toBe([8 => ['order' => 0, 'Title' => 'Foo']]);
});

it('stores to-many relationship replacement pivot data', function (): void {

    $pivot = [
        ':-)' => ['order' => 0, 'Title' => 'Smile'],
        '¯\_(ツ)_/¯' => ['order' => 1, 'Title' => 'Meh'],
        '( ͡° ͜ʖ ͡°)' => ['order' => 2, 'Title' => 'Glance'],
    ];

    $relationships = new Relationships([], ['foo' => 'int|string|null']);
    $relationships->replaceToMany('foo', [':-)', '¯\_(ツ)_/¯', '( ͡° ͜ʖ ͡°)'], $pivot);

    expect($relationships->getMetaData('foo'))->toBe($pivot);
});

it('retrieves to-many relationship mixed pivot data', function (): void {

    $relationships = new Relationships([], ['foo' => 'int|string|null']);
    $relationships->replaceToMany('foo', [':-)', '¯\_(ツ)_/¯', '( ͡° ͜ʖ ͡°)'], [
        ':-)' => ['order' => 0, 'Title' => 'Smile'],
        '¯\_(ツ)_/¯' => ['order' => 1, 'Title' => 'Meh'],
        '( ͡° ͜ʖ ͡°)' => ['order' => 2, 'Title' => 'Glance'],
    ]);
    $relationships->addToManyRelation('foo', 8, ['order' => 3, 'Title' => 'Foo']);

    expect($relationships->getMetaData('foo'))->toBe([
        ':-)' => ['order' => 0, 'Title' => 'Smile'],
        '¯\_(ツ)_/¯' => ['order' => 1, 'Title' => 'Meh'],
        '( ͡° ͜ʖ ͡°)' => ['order' => 2, 'Title' => 'Glance'],
        8 => ['order' => 3, 'Title' => 'Foo'],
    ]);
});

it('serialises to an array', function (): void {

    $relationships = new Relationships(['foo' => 'string'], ['bar' => 'string']);
    $relationships->setOneRelated('foo', ':-)');
    $relationships->replaceToMany('bar', ['¯\_(ツ)_/¯']);
    $relationships->addToManyRelation('bar', 'ʕ•ᴥ•ʔ');

    expect($relationships->toArray())->toBe(['foo' => ':-)', 'bar' => ['¯\_(ツ)_/¯', 'ʕ•ᴥ•ʔ']]);
});
