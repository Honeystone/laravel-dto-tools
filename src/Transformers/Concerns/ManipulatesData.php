<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Transformers\Concerns;

use Honeystone\DtoTools\Contracts\Transferable;
use Illuminate\Database\Eloquent\Model;

use function array_key_exists;

/**
 * @template TModel of Model
 * @template TData of Transferable
 */
trait ManipulatesData
{
    /**
     * @var array<string>
     */
    private array $excluded = [];

    /**
     * @var array<string, mixed>
     */
    private array $overridden = [];

    /**
     * @return $this<TModel, TData>
     */
    public function exclude(string ...$attributes): self
    {
        $this->excluded = $attributes;

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return $this<TModel, TData>
     */
    public function override(array $attributes): self
    {
        $this->overridden = $attributes;

        return $this;
    }

    /**
     * @param array<string, mixed> $mapped
     *
     * @return array<string, mixed>
     */
    final protected function processData(array $mapped): array
    {
        $processing = $mapped;

        foreach ($this->excluded as $attribute) {
            if (array_key_exists($attribute, $processing)) {
                unset($processing[$attribute]);
            }
        }

        foreach ($this->overridden as $attribute => $value) {
            $processing[$attribute] = $value;
        }

        $this->excluded = [];
        $this->overridden = [];

        return $processing;
    }
}
