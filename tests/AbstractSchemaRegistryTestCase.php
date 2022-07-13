<?php

namespace Jobcloud\SchemaConsole\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class AbstractSchemaRegistryTestCase extends TestCase
{
    /**
     * @param string $class
     * @param array|null $methodMap (array of method returns, or NULL)
     *     1. NULL - Methods won't be mocked, it will run original code
     *     2. Array of method names - ['method_name' => 'method_value',...].
     *        Specified method will be mocked with return value you set as element value.
     *        Those not specified will run original code
     *
     * @return MockObject
     */
    final public function makeMock(string $class, array $methodMap = []): MockObject
    {
        $mockBuilder = $this->getMockBuilder($class)->disableOriginalConstructor();

        if (empty($methodMap)) {
            return $mockBuilder->getMock();
        }

        $methodNames = array_merge(
            array_keys(
                array_filter(
                    $methodMap,
                    static function ($key) {
                        return !is_numeric($key);
                    },
                    ARRAY_FILTER_USE_KEY
                )
            ),
            array_values(
                array_filter(
                    $methodMap,
                    static function ($key) {
                        return is_numeric($key);
                    },
                    ARRAY_FILTER_USE_KEY
                )
            )
        );

        $mock = $mockBuilder->onlyMethods($methodNames)->getMock();

        foreach ($methodMap as $methodName => $value) {
            if (is_callable($value)) {
                $value($mock->method($methodName));
                continue;
            }

            if (is_numeric($methodName)) {
                $mock->method($value);
                continue;
            }

            if ($value instanceof Throwable) {
                $mock->method($methodName)->willThrowException($value);
                continue;
            }

            $mock->method($methodName)->willReturn($value);
        }

        return $mock;
    }

    /**
     * @param string $className
     * @param array $array
     */
    protected static function assertArrayHasInstanceOf(string $className, array $array): void
    {
        $filtered = array_filter($array, static function ($item) use ($className) {
            return $item instanceof $className;
        });

        self::assertGreaterThan(0, count($filtered));
    }
}
