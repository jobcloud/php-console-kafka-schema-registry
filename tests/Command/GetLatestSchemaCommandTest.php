<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Jobcloud\SchemaConsole\Command\GetLatestSchemaCommand;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GetLatestSchemaCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const SCHEMA_TEST_FILE = '/tmp/test.avsc';

    public function testCommand():void
    {
        $schema = '{}';

        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'getSchemaByVersion' => $schema,
        ]);

        $application = new Application();
        $application->add(new GetLatestSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:get:schema:latest');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => 'SomeSchemaName',
            'outputFile' => self::SCHEMA_TEST_FILE,
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(sprintf('Schema successfully written to %s.', self::SCHEMA_TEST_FILE), $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());

        $outputFileContents = file_get_contents(self::SCHEMA_TEST_FILE);
        self::assertEquals($schema, $outputFileContents);
    }

    public function testMissingSchema():void
    {
        $clientException = new ClientException(
            '',
            new Request('POST', '/'),
            new Response(404)
        );

        $expectedSchemaName = 'SomeSchemaName';

        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'getSchemaByVersion' => $clientException
        ]);

        $application = new Application();
        $application->add(new GetLatestSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:get:schema:latest');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => $expectedSchemaName,
            'outputFile' => self::SCHEMA_TEST_FILE,
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(sprintf('Schema %s does not exist', $expectedSchemaName), $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testUnknownClientErrorCodeThrowsException():void
    {
        $clientException = new ClientException(
            'ERROR MESSAGE',
            new Request('POST', '/'),
            new Response(401)
        );

        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'getSchemaByVersion' => $clientException
        ]);

        $application = new Application();
        $application->add(new GetLatestSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:get:schema:latest');
        $commandTester = new CommandTester($command);

        self::expectException(ClientException::class);
        self::expectExceptionMessage('ERROR MESSAGE');

        $commandTester->execute([
            'schemaName' => 'SomeSchemaName',
            'outputFile' => self::SCHEMA_TEST_FILE,
        ]);
    }

    public function testFailWriteToFile():void
    {
        $failurePath = '..';

        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'getSchemaByVersion' => '{}',
        ]);

        $application = new Application();
        $application->add(new GetLatestSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:get:schema:latest');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => 'SomeSchemaName',
            'outputFile' => $failurePath,
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(sprintf('Was unable to write schema to %s.', $failurePath), $commandOutput);
        self::assertEquals(1, $commandTester->getStatusCode());
    }
}