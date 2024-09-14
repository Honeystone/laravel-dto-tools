<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;

use function in_array;
use function is_array;
use function method_exists;

trait SerializesToArray
{
    /**
     * @return array<string, mixed>
     */
    final public function toRawArray(): array
    {
        $skip = method_exists($this, 'excludeFromSerialization') ?
            $this->excludeFromSerialization() :
            ['keyProperty'];

        $serialized = [];

        $reflectionClass = new ReflectionClass($this);

        foreach ($reflectionClass->getProperties() as $property) {

            if (!in_array($property->getName(), $skip)) {
                $serialized[$property->getName()] = $property->getValue($this);
            }
        }

        return $serialized;
    }

    /**
     * @return array<string, mixed>
     */
    final public function toArray(): array
    {
        return $this->processOutgoing($this->toRawArray());
    }

    /**
     * @param array<string, mixed> $serialized
     *
     * @return array<string, mixed>
     */
    final protected function processOutgoing(array $serialized): array
    {
        if (method_exists($this, 'transformOutgoing')) {
            $serialized = $this->transformOutgoing($serialized);
        }

        return $this->parseOutgoing($serialized);
    }

    /**
     * @param array<string, mixed> $serialized
     *
     * @return array<string, mixed>
     */
    private function parseOutgoing(array $serialized): array
    {
        foreach ($serialized as $name => $value) {

            if ($value instanceof Arrayable) {
                $serialized[$name] = $value->toArray();
            }

            if (is_array($value)) {
                $serialized[$name] = $this->parseOutgoing($value);
            }
        }

        return $serialized;
    }
}
