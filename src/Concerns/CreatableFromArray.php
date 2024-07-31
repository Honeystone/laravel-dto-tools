<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Concerns;

use Honeystone\DtoTools\Casters\Contracts\CastsValues;
use ReflectionAttribute;
use ReflectionClass;

use function array_key_exists;
use function is_array;
use function method_exists;

trait CreatableFromArray
{
    final public static function make(mixed ...$parameters): static
    {
        if (is_array($parameters[0] ?? null)) {
            $parameters = $parameters[0];
        }

        return new static(...self::processIncoming($parameters));
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    final protected static function processIncoming(array $parameters): array
    {
        if (method_exists(static::class, 'transformIncoming')) {
            $parameters = static::transformIncoming($parameters);
        }

        return self::parseIncoming($parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private static function parseIncoming(array $parameters): array
    {
        $reflectionClass = new ReflectionClass(static::class);

        foreach ($reflectionClass->getProperties() as $property) {

            $attributes = $property->getAttributes(
                CastsValues::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            foreach ($attributes as $attribute) {

                $name = $property->getName();

                if (!array_key_exists($name, $parameters)) {
                    continue;
                }

                $parameters[$name] = $attribute->newInstance()->cast($parameters[$name]);
            }
        }

        return $parameters;
    }
}
