<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClient;
use Jobcloud\SchemaConsole\Command\ListVersionsForSchemaCommand;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ListVersionForSchemaCommandTest extends AbstractSchemaRegistryTestCase
{

    public function testCommand():void
    {
        /** @var MockObject|KafkaSchemaRegistryApiClient $schemaRegistryApi */
        $schemaRegistryApi = $this->makeMock(KafkaSchemaRegistryApiClient::class, [
            'getAllSubjectVersions' => [1,2,3,4],
        ]);

        $application = new Application();
        $application->add(new ListVersionsForSchemaCommand($schemaRegistryApi));
        $command = $application->find('kafka-schema-registry:list:versions');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'schemaName' => 'someName'
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertEquals(implode(PHP_EOL, [1,2,3,4]), $commandOutput);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}