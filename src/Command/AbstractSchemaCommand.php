<?php

namespace Jobcloud\SchemaConsole\Command;

use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSchemaCommand extends Command
{
    /**
     * @var string
     */
    protected $registryUrl;

    /**
     * @var CachedRegistry
     */
    protected $registry;

    /**
     * @param string $registryUrl
     */
    public function __construct(string $registryUrl)
    {
        parent::__construct();
        $this->registryUrl = $registryUrl;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->registry = new BlockingRegistry(
            new PromisingRegistry(
                new Client(['base_uri' => $this->registryUrl])
            )
        );
    }
}