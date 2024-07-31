<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers;

use Honeystone\DtoTools\Contracts\Transferable;
use Honeystone\DtoTools\Transformers\Concerns\ManipulatesData;
use Honeystone\DtoTools\Transformers\Concerns\TransformsRelations;
use Honeystone\DtoTools\Transformers\Contracts\MakesTransformers;
use Honeystone\DtoTools\Transformers\Contracts\TransformsCollections;
use Honeystone\DtoTools\Transformers\Contracts\TransformsModels;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use function is_array;
use function method_exists;
use function property_exists;

/**
 * @template TModel of Model
 * @template TData of Transferable
 *
 * @implements TransformsModels<TModel, TData>
 * @implements TransformsCollections<TModel, TData>
 */
abstract class ModelTransformer implements TransformsModels, TransformsCollections
{
    /**
     * @use ManipulatesData<TModel, TData>
     */
    use ManipulatesData;

    /**
     * @use TransformsRelations<TModel>
     */
    use TransformsRelations;

    /**
     * @var class-string<TData>
     */
    protected string $dataClass;

    protected string $dataKeyName = 'id';

    public function __construct(private readonly MakesTransformers $transformerFactory)
    {
    }

    /**
     * @param TModel $model
     * @param mixed ...$parameters
     *
     * @return TData
     */
    public function transform(Model $model, mixed ...$parameters): Transferable
    {
        if (is_array($parameters[0] ?? null)) {
            $parameters = $parameters[0];
        }

        return $this->makeData(
            $this->processData([
                $this->dataKeyName => $this->getModelKey($model),
                ...$this->mapModel($model, $parameters),
            ]),
        );
    }

    /**
     * @param EloquentCollection<int, TModel> $models
     * @param mixed ...$parameters
     *
     * @return Collection<int, TData>
     */
    public function transformCollection(EloquentCollection $models, mixed ...$parameters): Collection
    {
        /** @phpstan-ignore-next-line */
        return $models
            ->map(fn (Model $model): Transferable => $this->transform($model, ...$parameters))
            ->values()
            ->collect();
    }

    protected function getModelKey(Model $model): int|string
    {
        return $model->getKey();
    }

    protected function getDataClass(): string
    {
        return $this->dataClass;
    }

    /**
     * @param TModel $model
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    final protected function mapModel(Model $model, array $parameters): array
    {
        if (method_exists($this, 'map')) {
            return $this->map($model, ...$parameters);
        }

        if (property_exists($this, 'only')) {
            return $model->only($this->only);
        }

        return $model->toArray();
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return TData
     */
    final protected function makeData(array $attributes): Transferable
    {
        return $this->dataClass::make(...$attributes);
    }

    final protected function getTransformerFactory(): MakesTransformers
    {
        return $this->transformerFactory;
    }
}
