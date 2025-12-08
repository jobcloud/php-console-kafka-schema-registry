<?php

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractSchemaCommand extends Command
{
    public function __construct(protected KafkaSchemaRegistryApiClientInterface $schemaRegistryApi)
    {
        parent::__construct();
    }
}
