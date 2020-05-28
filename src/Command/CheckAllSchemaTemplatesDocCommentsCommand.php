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
 * Class CheckAllSchemaTemplatesDocCommentsCommand
 */
class CheckAllSchemaTemplatesDocCommentsCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:check:template:doc:all')
            ->setDescription('Checks for doc comments for all schema templates in folder')
            ->setHelp('Checks for doc comments for all schema templates in folder')
            ->addArgument(
                'schemaTemplateDirectory',
                InputArgument::REQUIRED,
                'Path to avro schema template directory'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     * @throws RequestException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $directory */
        $directory = $input->getArgument('schemaTemplateDirectory');
        $avroFiles = SchemaFileHelper::getAvroFiles($directory);

        $io = new SymfonyStyle($input, $output);

        $failed = [];

        if (false === $this->checkDocCommentsOnSchemaTemplates($avroFiles, $failed)) {
            $io->error('Following schema templates do not have doc comments on all fields');
            $io->listing($failed);

            return 1;
        }

        $io->success('All schema templates have doc comments on all fields');

        return 0;
    }


    /**
     * @param array $avroFiles
     * @param array $failed
     * @return boolean
     */
    private function checkDocCommentsOnSchemaTemplates(array $avroFiles, array &$failed = []): bool
    {
        $failed = [];

        foreach ($avroFiles as $schemaName => $avroFile) {

            /** @var string $localSchema */
            $localSchema = file_get_contents($avroFile);

            $schema = json_decode($localSchema, true, 512, JSON_THROW_ON_ERROR);

            if (false === SchemaFileHelper::checkDocCommentsOnSchemaTemplates($schema)) {
                $failed[] = $schemaName;
            }
        }

        return 0 === count($failed);
    }
}
