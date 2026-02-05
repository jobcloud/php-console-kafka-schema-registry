<?php

namespace Jobcloud\SchemaConsole\Tests\Command;

use Jobcloud\SchemaConsole\Command\CheckDocCommentsCommand;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Jobcloud\SchemaConsole\Tests\AbstractSchemaRegistryTestCase;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CheckDocCommentsCommand::class)]
#[CoversClass(SchemaFileHelper::class)]
class CheckDocCommentsCommandTest extends AbstractSchemaRegistryTestCase
{
    protected const string SCHEMA_TEST_FILE = '/tmp/test.avsc';

    protected const string GOOD_SCHEMA = <<<EOF
{
  "type": "record",
  "name": "test",
  "namespace": "ch.jobcloud",
  "fields": [
    {
      "name": "name",
      "type": "string",
      "doc": "some desc"
    }
  ]
}
EOF;

    protected const string BAD_SCHEMA = <<<EOF
{
  "type": "record",
  "name": "test",
  "namespace": "ch.jobcloud",
  "fields": [
    {
      "name": "name",
      "type": "string"
    }
  ]
}
EOF;

    protected const string BAD_SCHEMA1 = <<<EOF
{
  "type": "record",
  "name": "test",
  "namespace": "ch.jobcloud"
  "fields": [
    {
      "name": "name",
      "type": "string",
      "doc": "some desc"
    }
  ]
}
EOF;

    protected const string BAD_SCHEMA2 = <<<EOF
{
  "type": "record",
  "name": "test",
  "namespace": "ch.jobcloud",
  "fields": [
    {
      "name": "name",
      "type": "string",
      "doc": " "
    }
  ]
}
EOF;

    public function testCommandSuccess(): void
    {
        $application = new Application();
        $application->addCommand(new CheckDocCommentsCommand());

        $command = $application->find('kafka-schema-registry:check:template:doc');
        $commandTester = new CommandTester($command);

        file_put_contents(self::SCHEMA_TEST_FILE, self::GOOD_SCHEMA);

        $commandTester->execute([
            'schemaTemplateFile' => self::SCHEMA_TEST_FILE
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Schema template has doc comments on all fields', $commandOutput);
        self::assertSame(0, $commandTester->getStatusCode());
    }

    public function testCommandBadSchema(): void
    {
        $application = new Application();
        $application->addCommand(new CheckDocCommentsCommand());

        $command = $application->find('kafka-schema-registry:check:template:doc');
        $commandTester = new CommandTester($command);

        file_put_contents(self::SCHEMA_TEST_FILE, self::BAD_SCHEMA);

        $commandTester->execute([
            'schemaTemplateFile' => self::SCHEMA_TEST_FILE
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Schema template does not have doc comments on all fields', $commandOutput);
        self::assertSame(1, $commandTester->getStatusCode());
    }

    public function testCommandBadSchema1(): void
    {
        $application = new Application();
        $application->addCommand(new CheckDocCommentsCommand());

        $command = $application->find('kafka-schema-registry:check:template:doc');
        $commandTester = new CommandTester($command);

        file_put_contents(self::SCHEMA_TEST_FILE, self::BAD_SCHEMA1);

        self::expectException(JsonException::class);

        $commandTester->execute([
            'schemaTemplateFile' => self::SCHEMA_TEST_FILE
        ]);
    }

    public function testCommandBadSchema2(): void
    {
        $application = new Application();
        $application->addCommand(new CheckDocCommentsCommand());

        $command = $application->find('kafka-schema-registry:check:template:doc');
        $commandTester = new CommandTester($command);

        file_put_contents(self::SCHEMA_TEST_FILE, self::BAD_SCHEMA2);

        $commandTester->execute([
            'schemaTemplateFile' => self::SCHEMA_TEST_FILE
        ]);

        $commandOutput = trim($commandTester->getDisplay());

        self::assertStringContainsString('Schema template does not have doc comments on all fields', $commandOutput);
        self::assertSame(1, $commandTester->getStatusCode());
    }
}
