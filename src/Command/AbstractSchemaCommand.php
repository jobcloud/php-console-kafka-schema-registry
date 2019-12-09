<?php

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractSchemaCommand extends Command
{

    /**
     * @var KafkaSchemaRegistryApiClientInterface
     */
    protected $schemaRegistryApi;

    /**
     * @param KafkaSchemaRegistryApiClientInterface $schemaRegistryApi
     */
    public function __construct(KafkaSchemaRegistryApiClientInterface $schemaRegistryApi)
    {
        parent::__construct();
        $this->schemaRegistryApi = $schemaRegistryApi;
    }
}
