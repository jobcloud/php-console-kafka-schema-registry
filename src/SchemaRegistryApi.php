<?php

namespace Jobcloud\SchemaConsole;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectsRequest;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectVersionsRequest;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityAgainstVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\defaultCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\deleteSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\singleSubjectVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\subjectCompatibilityLevelRequest;

class SchemaRegistryApi
{
    /**
     * @var ClientInterface
     */
    private $client;


    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    protected function parseJsonResponse(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getAllSchemas(): array
    {
        return $this->parseJsonResponse($this->client->send(allSubjectsRequest()));
    }

    /**
     * @param string $schemaName
     * @return array
     * @throws GuzzleException
     */
    public function getAllSchemaVersions(string $schemaName): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                allSubjectVersionsRequest($schemaName)
            )
        );
    }

    /**
     * @param string $schemaName
     * @param string $version
     * @return array
     * @throws GuzzleException
     */
    public function getSchemaByVersion(string $schemaName, string $version): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                singleSubjectVersionRequest($schemaName, $version)
            )
        );
    }

    /**
     * @param string $schema
     * @param string $schemaName
     * @return array
     * @throws GuzzleException
     */
    public function createNewSchemaVersion(string $schema, string $schemaName): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                registerNewSchemaVersionWithSubjectRequest($schema, $schemaName)
            )
        );
    }

    /**
     * @param string $schema
     * @param string $schemaName
     * @param string $version
     * @return bool
     * @throws GuzzleException
     */
    public function checkSchemaCompatibilityForVersion(
        string $schema,
        string $schemaName,
        string $version
    ): bool
    {
        $result = $this->parseJsonResponse(
            $this->client->send(
                checkSchemaCompatibilityAgainstVersionRequest($schema, $schemaName, $version)
            )
        );

        return (bool) $result['is_compatible'];
    }

    /**
     * @param string $schemaName
     * @param string $schema
     * @return int|null
     * @throws GuzzleException
     */
    public function getVersionForSchema(string $schemaName, string $schema): ?int
    {
        try {
            $result = $this->parseJsonResponse(
                $this->client->send(
                    checkIfSubjectHasSchemaRegisteredRequest($schemaName, $schema)
                ));

            return (int) $result['version'];

        } catch (ClientException $e) {
            if( $e->getCode() !== 40403){
                return null;
            }

            throw $e;
        }
    }

    /**
     * @param string $schemaName
     * @return void
     * @throws GuzzleException
     */
    public function deleteSchema(string $schemaName): void {
        $this->client->send(deleteSubjectRequest($schemaName));
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    public function getDefaultCompatibilityLevel(): string {
        $result = $this->parseJsonResponse(
            $this->client->send(
                defaultCompatibilityLevelRequest()
            )
        );

        return $result['compatibilityLevel'];
    }

    /**
     * @param string $schemaName
     * @return string
     * @throws GuzzleException
     */
    public function getSchemaCompatibilityLevel(string $schemaName): string
    {
        $result = $this->parseJsonResponse(
            $this->client->send(
                subjectCompatibilityLevelRequest($schemaName)
            )
        );

        return $result['compatibilityLevel'];
    }
}