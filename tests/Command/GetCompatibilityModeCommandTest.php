<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\GetCompatibilityModeCommand;
use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GetCompatibilityModeCommandTest extends AbstractSchemaRegistryTestCase
{

    public function testCommand(): void
    {
        /** @var MockObject|SchemaRegistryApi $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(SchemaRegistryApi::class, [
            'getDefaultCompatibilityLevel' => 'BACKWARD'
        ]);

        $application = new Application();
        $application->add(new GetCompatibilityModeCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:get:compatibility:mode');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals('The registry\'s default compatibility mode is BACKWARD', $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}