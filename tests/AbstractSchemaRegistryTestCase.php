<?php

namespace Jobcloud\SchemaConsole\Tests;

use http\Client;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Throwable;

abstract class AbstractSchemaRegistryTestCase extends TestCase
{
    /**
     * @param $class
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
            array_keys(array_filter($methodMap, static function ($key){
                return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY
            )),
            array_values(array_filter($methodMap, static function ($key){
                return is_numeric($key);
                }, ARRAY_FILTER_USE_KEY
            ))
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
     * Mocks Guzzle client object
     *
     * @param string|null $responseContents (JSON String)
     * @param int $code
     * @return MockObject|ClientInterface
     */
    final protected function mockClient(string $responseContents = '', int $code = 200): MockObject {
        return $this->makeMock(Client::class, [
            'send' => $this->mockResponse($responseContents, $code)
        ]);
    }

    /**
     * @param string $responseContents
     * @param int $code
     * @return MockObject
     */
    final protected function mockResponse(string $responseContents = '', int $code = 200): MockObject
    {
        return $this->makeMock(Response::class, [
            'getBody' => $this->makeMock(Stream::class, [
                'getContents' => $responseContents,
            ]),
            'getStatusCode' => $code,
        ]);
    }

    /**
     * @param string $className
     * @param array $array
     */
    protected static function assertArrayHasInstanceOf(string $className, array $array):void
    {
        $filtered = array_filter($array, static function ($item) use ($className){
            return $item instanceof $className;
        });

        self::assertGreaterThan(0, count($filtered));
    }
}