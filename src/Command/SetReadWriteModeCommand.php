<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\Kafka\SchemaRegistryClient\KafkaSchemaRegistryApiClientInterface;

class SetReadWriteModeCommand extends AbstractModeCommand
{
    #[\Override]
    public function getMode(): string
    {
        return KafkaSchemaRegistryApiClientInterface::MODE_READWRITE;
    }
}
