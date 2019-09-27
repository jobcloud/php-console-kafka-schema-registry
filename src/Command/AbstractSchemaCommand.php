<?php

namespace Jobcloud\SchemaConsole\Command;

use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
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
     * @var array|null
     */
    protected $auth;

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
     * @param array  $auth
     */
    public function __construct(string $registryUrl, array $auth = null)
    {
        parent::__construct();
        $this->registryUrl = $registryUrl;
        $this->auth = $auth;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $clientConfig = ['base_uri' => $this->registryUrl];

        if (null !== $this->auth && true === isset($this->auth['username']) && true === isset($this->auth['password'])) {
            $clientConfig['auth'] = [$this->auth['username'], $this->auth['password']];
        }

        $this->client = new Client(['base_uri' => $this->registryUrl]);

        parent::initialize($input, $output);
        $this->registry = new CachedRegistry(
            new BlockingRegistry(
                new PromisingRegistry($this->client)
            ),
            new AvroObjectCacheAdapter()
        );
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function getJsonDataFromResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
