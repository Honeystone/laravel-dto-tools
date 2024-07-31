<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers\Contracts;

use Honeystone\DtoTools\Contracts\Transferable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface MakesTransformers
{
    /**
     * @template TModel of Model
     *
     * @param TModel $model
     *
     * @return TransformsModels<TModel, Transferable>
     */
    public function makeForModel(Model $model): TransformsModels;

    /**
     * @template TModel of Model
     *
     * @param Collection<int, TModel> $models
     *
     * @return TransformsCollections<TModel, Transferable>
     */
    public function makeForCollection(Collection $models): TransformsCollections;
}
