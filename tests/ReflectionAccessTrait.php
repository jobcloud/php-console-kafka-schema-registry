<?php

namespace Jobcloud\SchemaConsole\Tests;

use ReflectionException;

trait ReflectionAccessTrait
{
    /**
     * @throws \ReflectionException
     */
    final public function setProperty(object $object, string $propertyName, mixed $newProperty): void
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);

        $property->setValue($object, $newProperty);
    }

    /**
     * @throws \ReflectionException
     */
    final public function getPropertyValue(object $object, string $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);

        return $property->getValue($object);
    }

    /**
     * @param array<mixed> $parameters
     *
     * @throws \ReflectionException
     */
    final public function invokeMethod(
        object $object,
        string $methodName,
        array $parameters = []
    ): mixed {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }
}
