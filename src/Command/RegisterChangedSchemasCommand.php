<?php

namespace Jobcloud\SchemaConsole\Command;

use GuzzleHttp\Exception\RequestException;
use Jobcloud\SchemaConsole\Helper\Avro;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \FilesystemIterator;
use \SplFileInfo;
use \AvroSchema;
use \AvroSchemaParseException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectVersionsRequest;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityAgainstVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use const FlixTech\SchemaRegistryApi\Constants\VERSION_LATEST;


class RegisterChangedSchemasCommand extends AbstractSchemaCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('schema:registry:register:changed')
            ->setDescription('Register all changed schemas from a path')
            ->setHelp('Register all changed schemas from a path')
            ->addArgument('schemaDirectory', InputArgument::REQUIRED, 'Path to avro schema directory')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return integer
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = $input->getArgument('schemaDirectory');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );

        $avroFiles = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (Avro::FILE_EXTENSION !== $file->getExtension()) {
                continue;
            }

            $avroFiles[$file->getBasename('.' . Avro::FILE_EXTENSION)] = $file->getRealPath();
        }

        $abortRegister = false;

        while (false === $abortRegister) {
            foreach ($avroFiles as $schemaName => $avroFile) {

                $isRegistered = true;
                $latestSchema = null;
                $localSchema = json_encode(json_decode(file_get_contents($avroFile)));

                try {
                    $response = $this->client->send(
                        allSubjectVersionsRequest(
                            $schemaName
                        )
                    );

                    $schemaVersions = $this->getJsonDataFromResponse($response);

                    $lastKey = array_key_last($schemaVersions);
                    $latestVersion = $schemaVersions[$lastKey];
                } catch (RequestException $e) {
                    if (404 !== $e->getCode()) {
                        throw $e;
                    } else {
                        $isRegistered = false;
                    }
                }

                if (true === $isRegistered) {
                    $response = $this->client->send(
                        singleSubjectVersionRequest($schemaName, $latestVersion)
                    );
                    $latestSchema = $this->getJsonDataFromResponse($response)['schema'];
                }

                if (true === $isRegistered && $latestSchema === $localSchema) {
                    $output->writeln(sprintf('Schema %s has been skipped (no change)', $schemaName));
                    continue;
                }

                if (true === $isRegistered) {
                    $response = $this->client->send(
                        checkSchemaCompatibilityAgainstVersionRequest(
                            $localSchema,
                            $schemaName,
                            $latestVersion
                        )
                    );

                    $result = $this->getJsonDataFromResponse($response);

                    if (false === $result['is_compatible']) {
                        $output->writeln(sprintf('Schema %s has an incompatible change', $schemaName));
                        return -1;
                    }
                }

                try {
                    $schema = AvroSchema::parse($localSchema);
                } catch (AvroSchemaParseException $e) {
                    continue;
                }

                $this->registry->register($schemaName, AvroSchema::parse($localSchema));

                $output->writeln(sprintf('Successfully registered new version of schema %s', $schemaName));
            }


        }
        return 0;
    }
}
