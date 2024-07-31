<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers\Contracts;

use Honeystone\DtoTools\Contracts\Transferable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 * @template TData of Transferable
 *
 * @see Model
 * @see Transferable
 */
interface TransformsCollections extends ManipulatesSchema
{
    /**
     * @param EloquentCollection<int, TModel> $models
     * @param mixed ...$parameters
     *
     * @return Collection<int, TData>
     */
    public function transformCollection(EloquentCollection $models, mixed ...$parameters): Collection;
}
