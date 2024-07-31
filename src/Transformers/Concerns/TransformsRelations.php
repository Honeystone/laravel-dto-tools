<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers\Concerns;

use Honeystone\DtoTools\Contracts\Transferable;
use Honeystone\DtoTools\Exceptions\RequiredRelationNotLoadedException;
use Honeystone\DtoTools\Transformers\Contracts\MakesTransformers;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use function array_key_exists;
use function is_array;

/**
 * @template TModel of Model
 */
trait TransformsRelations
{
    /**
     * @param TModel $model
     * @param mixed ...$parameters
     *
     * @return Collection<int, Transferable>|Transferable|null
     */
    final protected function requireRelated(
        string $relation,
        Model $model,
        mixed ...$parameters,
    ): Collection|Transferable|null {

        if (!$model->relationLoaded($relation)) {
            throw new RequiredRelationNotLoadedException($this->getDataClass(), $model::class, $relation);
        }

        return $this->includeRelated($relation, $model, ...$parameters);
    }

    /**
     * @param TModel $model
     * @param mixed ...$parameters
     *
     * @return Collection<int, Transferable>|Transferable|null
     */
    final protected function includeRelated(
        string $relation,
        Model $model,
        mixed ...$parameters,
    ): Collection|Transferable|null {

        if (is_array($parameters[0] ?? null)) {
            $parameters = $parameters[0];
        }

        if (!$model->relationLoaded($relation)) {
            return null;
        }

        $related = $model->$relation;

        if ($related === null) {
            return null;
        }

        if (array_key_exists('callback', $parameters)) {

            $parameters['callback']($related);

            unset($parameters['callback']);
        }

        if ($related instanceof Model) {
            return $this->transformRelatedModel($related, $parameters);
        }

        return $this->transformRelatedCollection($related, $parameters);
    }

    abstract protected function getDataClass(): string;

    abstract protected function getTransformerFactory(): MakesTransformers;

    /**
     * @param array<string, mixed> $parameters
     */
    final protected function transformRelatedModel(Model $model, array $parameters = []): Transferable
    {
        [$exclude, $override, $parameters] = $this->extractManipulations($parameters);

        return $this->getTransformerFactory()
            ->makeForModel($model)
            ->exclude(...$exclude)
            ->override($override)
            ->transform($model, ...$parameters);
    }

    /**
     * @param EloquentCollection<int, Model> $models
     * @param array<string, mixed> $parameters
     *
     * @return Collection<int, Transferable>
     */
    final protected function transformRelatedCollection(EloquentCollection $models, array $parameters = []): Collection
    {
        if ($models->isEmpty()) {
            return new Collection();
        }

        [$exclude, $override, $parameters] = $this->extractManipulations($parameters);

        return $this->getTransformerFactory()
            ->makeForCollection($models)
            ->exclude(...$exclude)
            ->override($override)
            ->transformCollection($models, ...$parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array{0: array<string>, 1: array<string, mixed>, 2: array<string, mixed>}
     */
    private function extractManipulations(array $parameters = []): array
    {
        $exclude = [];
        $override = [];

        if (array_key_exists('exclude', $parameters)) {
            $exclude = $parameters['exclude'];
            unset($parameters['exclude']);
        }

        if (array_key_exists('override', $parameters)) {
            $override = $parameters['override'];
            unset($parameters['override']);
        }

        return [$exclude, $override, $parameters];
    }
}
