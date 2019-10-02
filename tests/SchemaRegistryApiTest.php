<?php

namespace Jobcloud\SchemaConsole\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Throwable;

class SchemaRegistryApiTest extends AbstractSchemaRegistryTestCase
{
    use ReflectionAccessTrait;

    public function testSettingClient(): void
    {
        $expectedClient = new Client();
        $schemaRegistryApi = new SchemaRegistryApi($expectedClient);

        $actualClientSet = $this->getPropertyValue($schemaRegistryApi, 'client');

        self::assertSame($expectedClient, $actualClientSet);
    }

    public function testParseJsonResponse(): void
    {
        $schemaRegistryApiMock = $this
            ->getMockBuilder(SchemaRegistryApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->mockResponse('{ "a": "1" }');

        $result = $this->invokeMethod($schemaRegistryApiMock, 'parseJsonResponse', [$responseMock]);

        self::assertEquals(['a'=>1], $result);
    }

    public function testGetAllSchemas(): void
    {
        $this
            ->getSchemaRegistryApiWithClientCallExpectations('/subjects')
            ->getAllSchemas()
        ;
    }

    public function testGetAllSchemaVersions(): void
    {
        $schemaName = 'some-schema';

        $this
            ->getSchemaRegistryApiWithClientCallExpectations(sprintf('/subjects/%s/versions', $schemaName))
            ->getAllSchemaVersions($schemaName)
        ;
    }

    public function testGetSchemaByVersion(): void
    {
        $schemaName = 'some-schema';
        $version = '3';

        $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/subjects/%s/versions/%s', $schemaName, $version),
                '{"schema": "abc"}'
            )
            ->getSchemaByVersion($schemaName, $version)
        ;
    }

    public function testCreateNewSchemaVersion(): void
    {
        $schemaName = 'some-schema';
        $schema = '{}';

        $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/subjects/%s/versions', $schemaName)
            )
            ->createNewSchemaVersion($schema, $schemaName)
        ;
    }

    public function testCheckSchemaCompatibilityForVersionTrue(): void
    {
        $schemaName = 'some-schema';
        $schema = '{}';
        $version = '3';

        $result = $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/compatibility/subjects/%s/versions/%s', $schemaName, $version),
                '{"is_compatible": true}'
            )
            ->checkSchemaCompatibilityForVersion($schema, $schemaName, $version)
        ;

        self::assertTrue($result);
    }

    public function testCheckSchemaCompatibilityForVersionFalse(): void
    {
        $schemaName = 'some-schema';
        $schema = '{}';
        $version = '3';

        $result = $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/compatibility/subjects/%s/versions/%s', $schemaName, $version),
                '{"is_compatible": false}'
            )
            ->checkSchemaCompatibilityForVersion($schema, $schemaName, $version)
        ;

        self::assertFalse($result);
    }

    public function testGetVersionForSchema(): void
    {
        $schemaName = 'some-schema';
        $schema = '{}';

        $result = $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/subjects/%s', $schemaName),
                '{"version": 45}'
            )
            ->getVersionForSchema($schemaName,$schema)
        ;

        self::assertEquals(45, $result);
    }

    public function testDeleteSchema(): void
    {
        $schemaName = 'some-schema';

        $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/subjects/%s', $schemaName),
                '{"version": 45}'
            )
            ->deleteSchema($schemaName)
        ;
    }

    public function testGetDefaultCompatibilityLevel(): void
    {
        $result = $this
            ->getSchemaRegistryApiWithClientCallExpectations('/config', '{"compatibilityLevel": "BACKWARD"}')
            ->getDefaultCompatibilityLevel()
        ;

        self::assertEquals('BACKWARD', $result);
    }

    public function testGetSchemaCompatibilityLevel(): void
    {
        $schemaName = 'some-schema';

        $result = $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/config/%s', $schemaName),
                '{"compatibilityLevel": "BACKWARD"}'
            )
            ->getSchemaCompatibilityLevel($schemaName)
        ;

        self::assertEquals('BACKWARD', $result);
    }

    public function testGetLatestSchemaVersion(): void
    {
        $schemaName = 'some-schema';

        $result = $this
            ->getSchemaRegistryApiWithClientCallExpectations(
                sprintf('/subjects/%s/versions', $schemaName),
                '[1,2,3,4,5]'
            )
            ->getLatestSchemaVersion($schemaName)
        ;

        self::assertEquals(5, $result);
    }

    /**
     * @param string $expectedPath
     * @param mixed $clientSendReturn
     * @return SchemaRegistryApi
     */
    protected function getSchemaRegistryApiWithClientCallExpectations(
        string $expectedPath,
        $clientSendReturn = '{}'
    ): SchemaRegistryApi
    {
        $client = $this
            ->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();

        if ($clientSendReturn instanceof Throwable)
        {
            $client->method('send')->willThrowException($clientSendReturn);
        }

        if (is_string($clientSendReturn))
        {
            $client->method('send')->willReturn(new Response(200, [], $clientSendReturn));
        }

        $client
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Request $response) use ($expectedPath){
                return $expectedPath === $response->getUri()->getPath();
            }));

        return new SchemaRegistryApi($client);
    }
}