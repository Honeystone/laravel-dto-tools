<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers;

use Honeystone\DtoTools\Contracts\Transferable;
use Honeystone\DtoTools\Transformers\Contracts\MakesTransformers;
use Honeystone\DtoTools\Transformers\Contracts\TransformsCollections;
use Honeystone\DtoTools\Transformers\Contracts\TransformsModels;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

use function app;
use function array_key_exists;

final readonly class TransformerFactory implements MakesTransformers
{
    /**
     * @param array<
     *     class-string<Model>,
     *     class-string<TransformsModels<Model, Transferable>|TransformsCollections<Model, Transferable>>
     * > $map
     */
    public function __construct(private array $map)
    {
    }

    public function makeForModel(Model $model): TransformsModels
    {
        return app($this->getTransformerClass($model::class));
    }

    public function makeForCollection(Collection $models): TransformsCollections
    {
        if ($models->isEmpty()) {
            throw new RuntimeException('Unable to determine mapping model from empty collection.');
        }

        return app($this->getTransformerClass($models->first()::class));
    }

    /**
     * @template TModel of Model
     *
     * @param class-string<TModel> $modelClass
     *
     * @return class-string<TransformsModels<TModel, Transferable>|TransformsCollections<TModel, Transferable>>
     */
    private function getTransformerClass(string $modelClass): string
    {
        if (!array_key_exists($modelClass, $this->map)) {
            throw new RuntimeException(
                "The {$modelClass} model must have a configured transformer before this factory can make it.",
            );
        }

        return $this->map[$modelClass];
    }
}
