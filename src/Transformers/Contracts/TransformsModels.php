<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers\Contracts;

use Honeystone\DtoTools\Contracts\Transferable;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @template TData of Transferable
 */
interface TransformsModels extends ManipulatesSchema
{
    /**
     * @param TModel $model
     * @param mixed ...$parameters
     *
     * @return TData
     */
    public function transform(Model $model, mixed ...$parameters): Transferable;
}
