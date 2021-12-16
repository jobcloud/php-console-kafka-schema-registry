<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Jobcloud\SchemaConsole\Helper\SchemaFileHelper;
use JsonException;
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
            ->setName('kafka-schema-registry:check:template:doc')
            ->setDescription('Checks schema template doc comments')
            ->setHelp('Checks schema template doc comments')
            ->addArgument('schemaTemplateFile', InputArgument::REQUIRED, 'Path to Avro template schema file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     * @throws JsonException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $errorMessage = 'Schema template does not have doc comments on all fields';

        /** @var string $schemaFile */
        $schemaFile = $input->getArgument('schemaTemplateFile');

        $io = new SymfonyStyle($input, $output);

        /** @var string $localSchema */
        $localSchema = file_get_contents($schemaFile);

        $schema = json_decode($localSchema, true, 512, JSON_THROW_ON_ERROR);

        if (false === SchemaFileHelper::checkDocCommentsOnSchemaTemplates($schema)) {
            $io->error($errorMessage);

            return 1;
        }

        $io->success('Schema template has doc comments on all fields');

        return 0;
    }
}
