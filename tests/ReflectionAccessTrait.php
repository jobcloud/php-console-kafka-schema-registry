<?php

namespace Jobcloud\SchemaConsole\Tests;

use ReflectionClass;
use ReflectionException;

/**
 * Trait ReflectionAccessTrait
 * @package Jobcloud\ERecruiterApiClient\Tests
 */
trait ReflectionAccessTrait
{
    /**
     * @throws ReflectionException
     */
    final public function setProperty(object $object, string $propertyName, mixed $newProperty): void
    {
        $reflection = new ReflectionClass($object::class);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($object, $newProperty);
    }

    /**
     * @throws ReflectionException
     */
    final public function getPropertyValue(object $object, string $propertyName): mixed
    {
        $reflection = new ReflectionClass($object::class);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param array $parameters Array of parameters to pass into method.
     *
     * @throws ReflectionException
     */
    final public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass($object::class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
