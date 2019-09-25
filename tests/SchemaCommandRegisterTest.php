<?php

namespace Jobcloud\SchemaConsole\Tests;

use Jobcloud\SchemaConsole\Command\CheckCompatibilityCommand;
use Jobcloud\SchemaConsole\Command\CheckIsRegistredCommand;
use Jobcloud\SchemaConsole\Command\GetCompatibilityModeCommand;
use Jobcloud\SchemaConsole\Command\GetLatestSchemaCommand;
use Jobcloud\SchemaConsole\Command\GetSchemaByVersionCommand;
use Jobcloud\SchemaConsole\Command\ListAllSchemasCommand;
use Jobcloud\SchemaConsole\Command\ListVersionsForSchemaCommand;
use Jobcloud\SchemaConsole\Command\RegisterChangedSchemasCommand;
use Jobcloud\SchemaConsole\Command\RegisterSchemaVersionCommand;
use Jobcloud\SchemaConsole\SchemaCommandRegister;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

class SchemaCommandRegisterTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Application();
    }

    public function testRegister(): void
    {
        $expectedCommandClasses = [
            GetCompatibilityModeCommand::class,
            RegisterSchemaVersionCommand::class,
            ListAllSchemasCommand::class,
            ListVersionsForSchemaCommand::class,
            GetSchemaByVersionCommand::class,
            CheckCompatibilityCommand::class,
            CheckIsRegistredCommand::class,
            GetLatestSchemaCommand::class,
            RegisterChangedSchemasCommand::class,
        ];

        self::assertCount(2, $this->application->all());
        SchemaCommandRegister::register($this->application, 'some-url');
        self::assertCount(count($expectedCommandClasses) + 2, $this->application->all());

        foreach($expectedCommandClasses as $expectedCommandClass) {
            self::assertSame(
                count(
                    array_filter(
                        $this->application->all(),
                        static function ($command) use ($expectedCommandClass)
                        {
                            return $command instanceof $expectedCommandClass;
                        }
                        )
                ), 1
            );
        }


    }
}