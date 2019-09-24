<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\defaultCompatibilityLevelRequest;

class GetCompatibilityModeCommand extends AbstractSchemaCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:get:compatibility:mode')
            ->setDescription('Get the default compatibility mode of the registry')
            ->setHelp('Get the default compatibility mode of the registry');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->client->send(defaultCompatibilityLevelRequest());

        $data = $this->getJsonDataFromResponse($response);

        var_dump($data);

        /*$output->writeln(
            sprintf('Schema is %s', $data['is_compatible'] ? 'Compatible' : 'NOT Compatible')
        );*/

        return 0;
    }
}
