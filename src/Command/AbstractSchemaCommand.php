<?php

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\SchemaConsole\SchemaRegistryApi;
use Symfony\Component\Console\Command\Command;

abstract class AbstractSchemaCommand extends Command
{
    /**
     * @var SchemaRegistryApi
     */
    protected $schemaRegistryApi;

    /**
     * @param SchemaRegistryApi $schemaRegistryApi
     */
    public function __construct(SchemaRegistryApi $schemaRegistryApi)
    {
        parent::__construct();
        $this->schemaRegistryApi = $schemaRegistryApi;
    }
}
