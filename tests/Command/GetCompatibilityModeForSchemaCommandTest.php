<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\GetCompatibilityModeForSchemaCommand;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GetCompatibilityModeForSchemaCommandTest extends AbstractSchemaRegistryTestCase
{

    public function testCommand():void
    {
        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'getSchemaCompatibilityLevel' => 'BACKWARD',
        ]);

        $application = new Application();
        $application->add(new GetCompatibilityModeForSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:get:schema:compatibility:mode');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => 'SomeSchemaName',
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals('The schema\'s compatibility mode is BACKWARD', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}