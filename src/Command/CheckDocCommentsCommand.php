<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\RequestException;
use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

/**
 * Class CheckDocCommentsCommand
 */
class CheckDocCommentsCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:doc:comments')
            ->setDescription('Checks schema doc comments')
            ->setHelp('Checks schema doc comments')
            ->addArgument('schemaFile', InputArgument::REQUIRED, 'Path to Avro schema file');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws RequestException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $errorMessage = 'Schema does not have doc comments on all fields';

        /** @var string $schemaFile */
        $schemaFile = $input->getArgument('schemaFile');

        $io = new SymfonyStyle($input, $output);

        /** @var string $localSchema */
        $localSchema = file_get_contents($schemaFile);

        $schema = json_decode($localSchema, true, 512, JSON_THROW_ON_ERROR);

        if (false === SchemaFileHelper::hasDocCommentsOnAllFields($schema)) {
            $io->error($errorMessage);

            return 1;
        }

        $io->success('Schema has doc comments on all fields');

        return 0;
    }
}
