<?php

namespace Jobcloud\SchemaConsole\Command;

use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

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
     * @var Client
     */
    protected $client;

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
        $this->client = new Client(['base_uri' => $this->registryUrl]);

        parent::initialize($input, $output);
        $this->registry = new BlockingRegistry(
            new PromisingRegistry($this->client)
        );
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function getJsonDataFromResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)
    }
}