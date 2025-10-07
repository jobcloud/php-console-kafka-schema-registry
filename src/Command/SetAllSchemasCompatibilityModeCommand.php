<?php

declare(strict_types=1);

namespace Jobcloud\SchemaConsole\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetAllSchemasCompatibilityModeCommand extends AbstractSchemaCommand
{
    protected function configure(): void
    {
        $this
            ->setName('kafka-schema-registry:set:compatibility:mode:all')
            ->setDescription('Set compatibility modes for multiple schemas from a JSON configuration file')
            ->setHelp($this->getHelpText())
            ->addArgument(
                'configFile',
                InputArgument::REQUIRED, 'Path to JSON configuration file containing schema-compatibility mappings'
            );
    }

    private function getHelpText(): string
    {
        return <<<'HELP'
Set compatibility modes for multiple schemas based on configuration in a JSON file.

JSON File Format:
The configuration file must be a valid JSON array with the following structure:

[
  {
    "schemaName": "schema-subject-name",
    "compatibilityLevel": "COMPATIBILITY_LEVEL"
  }
]

Required Fields:
- schemaName: The subject name of the schema in the registry
- compatibilityLevel: One of the following compatibility levels:
  * NONE
  * BACKWARD
  * BACKWARD_TRANSITIVE
  * FORWARD
  * FORWARD_TRANSITIVE
  * FULL
  * FULL_TRANSITIVE

Example:
[
  {
    "schemaName": "user-events",
    "compatibilityLevel": "BACKWARD_TRANSITIVE"
  },
  {
    "schemaName": "order-events",
    "compatibilityLevel": "FORWARD"
  },
  {
    "schemaName": "payment-events",
    "compatibilityLevel": "FULL"
  }
]

The command will process each schema in the order specified and provide feedback
for each operation. If any schema update fails, the command will continue processing
the remaining schemas and return a non-zero exit code at the end.
HELP;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configFilePath = (string) $input->getArgument('configFile');

        $config = $this->loadConfigFile($configFilePath, $output);
        if (null === $config) {
            return 1;
        }

        $totalSchemas = count($config);
        $successCount = 0;
        $failureCount = 0;

        $output->writeln(sprintf('Processing %d schema compatibility configurations...', $totalSchemas));

        foreach ($config as $index => $schemaConfig) {
            if (false === $this->isValidSchemaConfig($schemaConfig)) {
                $output->writeln(
                    sprintf('Invalid configuration at index %d: missing schemaName or compatibilityLevel', $index)
                );
                $failureCount++;

                continue;
            }

            $schemaName = (string) $schemaConfig['schemaName'];
            $compatibilityLevel = (string) $schemaConfig['compatibilityLevel'];

            $output->write(
                sprintf('Setting compatibility mode for schema "%s" to "%s"... ', $schemaName, $compatibilityLevel)
            );

            if ($this->setSchemaCompatibility($schemaName, $compatibilityLevel, $output)) {
                $successCount++;

                continue;
            }

            $failureCount++;
        }

        $this->outputSummary($output, $totalSchemas, $successCount, $failureCount);

        return $failureCount > 0 ? 1 : 0;
    }

    private function loadConfigFile(string $configFilePath, OutputInterface $output): ?array
    {
        $jsonContent = @file_get_contents($configFilePath);
        if (false === $jsonContent) {
            $output->writeln(sprintf('Could not read configuration file: %s', $configFilePath));

            return null;
        }

        $config = json_decode($jsonContent, true);
        if (null === $config || false === is_array($config) || false === array_is_list($config)) {
            $output->writeln('Configuration file must contain a JSON array of schema configurations');

            return null;
        }

        return $config;
    }

    private function isValidSchemaConfig(mixed $schemaConfig): bool
    {
        return is_array($schemaConfig)
            && isset($schemaConfig['schemaName'])
            && isset($schemaConfig['compatibilityLevel']);
    }

    private function setSchemaCompatibility(string $schemaName, string $compatibilityLevel, OutputInterface $output): bool
    {
        try {
            $result = $this->schemaRegistryApi->setSubjectCompatibilityLevel($schemaName, $compatibilityLevel);

            if (true === $result) {
                $output->writeln('<info>SUCCESS</info>');
                return true;
            }

            $output->writeln('<error>FAILED</error>');

            return false;
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>FAILED: %s</error>', $e->getMessage()));

            return false;
        }
    }

    private function outputSummary(OutputInterface $output, int $total, int $success, int $failure): void
    {
        $output->writeln('');
        $output->writeln('=== Summary ===');
        $output->writeln(sprintf('Total schemas processed: %d', $total));
        $output->writeln(sprintf('Successful updates: %d', $success));
        $output->writeln(sprintf('Failed updates: %d', $failure));
    }
}
