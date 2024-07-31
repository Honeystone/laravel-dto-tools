# Honeystone DTO Tools for Laravel

![Static Badge](https://img.shields.io/badge/tests-passing-green)
![GitHub License](https://img.shields.io/github/license/honeystone/laravel-dto-tools)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/honeystone/laravel-dto-tools)](https://packagist.org/packages/honeystone/laravel-dto-tools)
![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/honeystone/laravel-dto-tools/php)
![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/honeystone/laravel-dto-tools/illuminate%2Fcontracts?label=laravel)
[![Static Badge](https://img.shields.io/badge/honeystone-fa6900)](https://honeystone.com)

DTO tools is a package designed to bring additional power and convenience to your native PHP data transfer objects. The
main motivation for this package was to remove much of the boilerplate created moving data in to and out of DTOs. For
example, transforming snake-cased model attributes to camel-cased to be consumed by your presentation layer, or casting
a user inputted numerical string (after validation ofc) to an integer.

Features include property casting and mutation, serialization, patch data handling, relationships, and model and
collection transformation.

## Support us

[![Support Us](https://honeystone.com/images/github/support-us.webp)](https://honeystone.com)

We are committed to delivering high-quality open source packages maintained by the team at Honeystone. If you would
like to support our efforts, simply use our packages, recommend them and contribute.

If you need any help with your project, or require any custom development, please [get in touch](https://honeystone.com/contact-us).

## Installation

```shell
composer require honeystone/laravel-dto-tools
```

Publish the configuration file with:

```shell
php artisan vendor:publish --tag=honeystone-dto-tools-config
```

## Usage

This package requires very little modification to your existing DTOs. As a minimum, your DTOs need to implement the
`Transferable` contract and use the `HasTransferableData` trait, like so:

```php
<?php

declare(strict_types=1);

namespace App\Domains\Articles\Data;

use App\Domains\Articles\Data\Enums\Status;
use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Contracts\Transferable;

final readonly class ArticleData implements Transferable
{
    use HasTransferableData

    public function __construct(
        public string $title,
        public ?string $description,
        public Status $status,
        public string $modified,
    ) {
    }
}
```

As you can see, this is still just a regular `readonly` PHP object. You can instantiate it normally too, however, if you
want to make use of property casting, you'll need to use the static `make()` method:

```php
$data = ArticleData::make(
    title: $source['title'],
    description: $source['description'] === '' ? null : $source['description'],
    status: $source['status'] === 'published' ? Status::PUBLISHED : Status::DRAFT,
    modified: Carbon::make($source['modified'])->toIso8601String(),
);
```

This is really messy, so we'll clean it up in the next section.

### Casting properties

Casters are used to intercept and cast/mutate values before instantiating the DTO. They are implemented using PHP
Attributes. In the following example, we'll ensure that our empty description is cast to `null`, that the status is cast
to an enum, and that the modified time is represented in an iso-8601 string:

```php
use App\Domains\Articles\Data\Enums\Status;
use Honeystone\DtoTools\Casters\DateTimeCaster;
use Honeystone\DtoTools\Casters\EnumCaster;
use Honeystone\DtoTools\Casters\NullCaster;

final readonly class ArticleData implements Transferable
{
    use HasTransferableData

    public function __construct(
        public string $title,

        #[NullCaster('')]
        public ?string $description,

        #[EnumCaster(Status::class)]
        public Status $status,

        #[DateTimeCaster]
        public string $modified,
    ) {
    }
}
```

Our data can be a little looser now, but we still benefit from the DTO's type safety:

```php
$data = ArticleData::make(
    title: $source['title'],
    description: $source['description'],
    status: $source['status'],
    modified: $source['modified'],
);

//or even cleaner
$data = ArticleData::make(Arr::only($source, 'title', 'description', 'status', 'modified'));
```

Much better. The following casters are available, or you can create your own:

```php
#[DateTimeCaster('Y-m-d')] //takes a format string, or defaults to iso-8601
#[EnumCaster(Status::class, State::class)] //takes one or more enum class strings
#[NullCaster('', '-')] //takes one or more values that should be converted to null
#[ScalarCaster('bool', 'null')] //takes one or more accepted types, if no types match it will cast to the last type
```

To implement your own casters, just create an `Attribute` that implements
`Honeystone\DtoTools\Casters\Contracts\CastsValues`.

```php
<?php

declare(strict_types=1);

namespace App\Support\Data\Casters;

use Attribute;
use Honeystone\DtoTools\Casters\Contracts\CastsValues;

#[Attribute]
final readonly class MyCaster implements CastsValues
{
    public function cast(mixed $value): mixed
    {
        //...
    }
}
```

### Serialization

There are two primary serialization methods available, `getAttributes()` and `toArray()`. The difference is that
`getAttributes()` will simply provide the property values in an array, whereas `toArray()` will recursively convert
`Transferable` and `Arrayable` properties into arrays.

There are also `getRelationships()` and `toStorableArray()`, but we'll talk about those later.

### Property transformation

Sometimes you need to make sweeping changes to all parameters entering your DTO. To achieve this you can add a static
`transformIncoming()` method to your DTO:

```php
final readonly class SomeData implements Transferable
{
    use HasTransferableData

    public function __construct(
        public string $foo,
        public string $bar,
    ) {
    }

    protected static function transformIncoming(array $parameters): array
    {
        return array_map(static fn (string $value): string => "🔥 $value 🔥", $parameters);
    }
}
```
```php
echo SomeData::make(['foo', 'bar'])->getAttributes(); //['foo' => '🔥 foo 🔥', 'bar' => '🔥 bar 🔥']
```

The `transformOutgoing()` method can also be implemented for DTO serialization:

```php
final readonly class SomeData implements Transferable
{
    use HasTransferableData

    public function __construct(
        public string $foo,
        public string $bar,
    ) {
    }

    protected static function transformOutgoing(array $parameters): array
    {
        return array_map(static fn (string $value): string => "🔥 $value 🔥", $parameters);
    }
}
```

```php
echo SomeData::make(['foo', 'bar'])->getAttributes(); //['foo' => 'foo',       'bar' => 'bar']
echo SomeData::make(['foo', 'bar'])->toArray();       //['foo' => '🔥 foo 🔥', 'bar' => '🔥 bar 🔥']
```

A very common use-case in Laravel will be converting snake-case properties to camel-case. As such, the
`CreatableFromSnake` and `SerializesToSnake` traits are available:

```php
use Honeystone\DtoTools\Concerns\CreatableFromSnake;
use Honeystone\DtoTools\Concerns\SerializesToSnake;

final readonly class SomeData implements Transferable
{
    use HasTransferableData, CreatableFromSnake, SerializesToSnake;

    public function __construct(
        public string $someProperty,
    ) {
    }
}
```
```php
echo SomeData::make(some_property: 'value')->getAttributes(); //['someProperty'  => 'value']
echo SomeData::make(some_property: 'value')->toArray();       //['some_property' => 'value']
```

### Storable data

We've looked at `Transferable` data so far, which is great for passing complete data through your service layer, but
what if you need to process partial data (i.e. a patch)? This is `Storable` enters the scene.

```php
<?php

declare(strict_types=1);

namespace App\Domains\Articles\Data;

use App\Domains\Articles\Data\Enums\Status;
use Honeystone\DtoTools\Concerns\HasStorableData;
use Honeystone\DtoTools\Contracts\Storable;

final class ArticlePatchData implements Storable
{
    use HasStorableData

    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly Status $status,
        public readonly string $modified,
    ) {
    }
}
```

A basic `Storable` is very similar to a `Transferable` except the class itself cannot be `readonly`. `Storable`s need to
have a little bit of state to function, so we mark our properties `readonly` instead.

By default, a storable isn't in patching mode. For this we need to add the `Patch` class attribute:

```php
<?php
use Honeystone\DtoTools\Attributes\Patch;

#[Patch]
final class ArticlePatchData implements Storable
{
    use HasStorableData

    public function __construct(
        //...
    ) {
    }
}
```

We can check if the storable is in patching mode using the `isPatching()` method.

When a storable is in patching mode, `null` values will be automatically excluded from serialisation using the
`toStorableArray()` method:

```php
echo SomeData::make(foo: 'value', bar: null)->toStorableArray(); //['foo' => 'value']
echo SomeData::make(foo: 'value', bar: null)->toArray();         //['foo' => 'value', 'bar => null]
```

You can also use the `isStorable()` method to check if an individual property can be stored:

```php
$data = echo SomeData::make(foo: 'value', bar: null);

$data->isStorable('foo'); //true
$data->isStorable('bar'); //false
```

Sometimes, `null` is a valid value and should be stored. In these cases you can use the `force()` method to mark these
properties as storable:

```php
$data = echo SomeData::make(foo: 'value', bar: null);

$data->force('bar');

$data->isStorable('bar'); //true
```

If you need a list of forced properties, use `getForced()`.

### Storable relationships

Occasionally, when transferring data into your services layer you need to represent changes to relational structures.
You could do this with a simple property on your DTO, for example:

```php
<?php
#[Patch]
final class ArticlePatchData implements Storable
{
    use HasStorableData

    /**
    * @param array<int>|null $tags
    */
    public function __construct(
        //...
        public ?array $tags = null,
    ) {
    }
}
```

There are a few problems with this approach though: there's no real type safety, you can't just add or remove a tag,
you have to provide all the tags, and you cant have any meta data (e.g. order). Maybe you upgrade this to be an array
of DTOs, but there's an easier way:

```php
<?php
use Honeystone\DtoTools\Attributes\ToMany;

#[Patch]
#[ToMany(['tags' => 'int|empty'])]
final class ArticlePatchData implements Storable
{
    use HasStorableData

    public function __construct(
        //...
    ) {
    }
}
```

Using the `ToMany` class attribute we can declare a to-many relationship called 'tags' that can be empty, or integers.

We can now add, remove and replace related tags:

```php
$data = ArticlePatchData::make(...);

$data->relationships()->addToManyRelation('foo', 123, ['priority' => 5]);
```

Relationships are stored using the related id. This can be either an integer or a string. You can also provide a
`Transferable` or `Storable` and the library will use its `getKey()` method to determine the id. Additional meta data
can be provided as an array or as a `Transferable`:

```php
$data = ArticlePatchData::make(...);

$data->relationships()->addToManyRelation('foo', TagData::make(...), TagMetaData::make(...));
```

The following relationship class `Attributes` are supported:

```php
#[ToOne(['foo' => 'int|string|null'])]
#[ToMany(['bar' => 'int|string|empty'])]
```

And the following relationship methods are available:

```php
$data->relationships()->hasToOne('foo');
$data->relationships()->getOneRelated('foo');
$data->relationships()->setOneRelated('foo', 123);
$data->relationships()->unsetOneRelated('foo');

$data->relationships()->hasToMany('bar');
$data->relationships()->getManyRelated('bar'); //plus any additions, minus any removals
$data->relationships()->getManyAdditions('bar');
$data->relationships()->getManyRemovals('bar');
$data->relationships()->addToManyRelation('bar', 123, []); //param 3 optional
$data->relationships()->removeToManyRelation('bar', 123);
$data->relationships()->replaceToMany('bar', 123, []); //param 3 optional, clears addition and removals
$data->relationships()->resetToMany('bar'); //clears addition and removals
$data->relationships()->getMetaData('bar');
$data->relationships()->setMetaData('bar', []);
```

Serialized relationships are included in `toStorableArray()`, or you can grab just the relationships with
`getRelationships()`.

### Model transformation

It's not uncommon to convert a `Model` into a DTO. They'll be different though. The data of a DTO should be more
specific and situational. This can lead to a lot of boilerplate to handle the transformations. This package includes an
`abstract` `ModelTransformer` to help clean this up.

Here's the most basic example:

```php
<?php

declare(strict_types=1);

namespace App\Domains\Articles\Data\Transformers;

use App\Domains\Articles\Data\ArticleData;
use Honeystone\DtoTools\Transformers\ModelTransformer;

final class ArticleTransformer extends ModelTransformer
{
    protected string $dataClass = ArticleData::class;
}
```

You can now call the `transform()` and `transformCollection()` methods of your transformer to transform your `Model`s to
`Transferable`s:

```php
$transformer = app(ArticleTransformer::class);

$transformer->transform(Article::first());
$transformer->transformCollection(Article::all());
```

Internally this example will use the `toArray()` method of your model.

We can be specific about which fields to include in the transformation using the `$only` property:

```php
final class ArticleTransformer extends ModelTransformer
{
    protected string $dataClass = ArticleData::class;

    protected array $only = [
        'title',
        'description',
        'status',
        'modified',
    ];
}
```

If we need to do something more complex, we could instead create a `map()` method:

```php
final class ArticleTransformer extends ModelTransformer
{
    protected string $dataClass = ArticleData::class;

    protected function map(Model $model): array
    {
        return [
            'title' => Str::title($model->title),
            'status' => in_array($model->status, ['published', 'active']) ? 'published' : 'draft',
            ...$model->only('description', 'modified'),
        ];
    }
}
```

We can also pass additional parameters to our map method:

```php
$transformer->transform(Article::first(), foo: 'bar');
$transformer->transformCollection(Article::all(), foo: 'bar', bar: 'baz');
```

The `override()` and `exclude()` method can be chained to allow on-the-fly changes to the transformation:

```php
$transformer
    ->exclude('foo', 'bar')
    ->override(['status' => 'preview'])
    ->transform(Article::first()); //transform() or transformCollection()
```

That's all good, but what about relationships:

```php
final class ArticleTransformer extends ModelTransformer
{
    protected string $dataClass = ArticleData::class;

    protected function map(Model $model): array
    {
        return [
            'title' => Str::title($model->title),
            'status' => in_array($model->status, ['published', 'active']) ? 'published' : 'draft',
            ...$model->only('description', 'modified'),
            'tags' => $this->includeRelated('tags', $model->tags),
            'category' => $this->requireRelated('category', $model->category),
        ];
    }
}
```

The `includeRelated()` and `requireRelated()` methods will convert a `Model` using a`ModelTransformer` based on the
transformation mappings in this package's config file.

The `requireRelated()` method will throw a `Honeystone\DtoTools\Exceptions\RequiredRelationNotLoadedException` if the
relationship has not been loaded.

You can pass additional parameters to these methods, which will be passed onto their respective `ModelTransformer`'s
`map()` method.

Any exclusions or overrides can also be included as additional parameters:

```php
final class ArticleTransformer extends ModelTransformer
{
    protected string $dataClass = ArticleData::class;

    protected function map(Model $model): array
    {
        return [
            //...
            'category' => $this->requireRelated(
                'category',
                $model->category,
                exclude: ['foo', 'bar'],
                override: ['status' => 'preview'],
                bar: 'baz', //passed onto map() method
            ),
        ];
    }
}
```

That's pretty much it! If you find this package useful, we'd love to hear from you.

## Changelog

A list of changes can be found in the [CHANGELOG.md](CHANGELOG.md) file.

## License

[MIT](LICENSE.md) © [Honeystone Consulting Ltd](https://honeystone.com)
