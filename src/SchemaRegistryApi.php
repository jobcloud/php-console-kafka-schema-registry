<?php

namespace Jobcloud\SchemaConsole;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectsRequest;
use function FlixTech\SchemaRegistryApi\Requests\allSubjectVersionsRequest;
use function FlixTech\SchemaRegistryApi\Requests\checkIfSubjectHasSchemaRegisteredRequest;
use function FlixTech\SchemaRegistryApi\Requests\checkSchemaCompatibilityAgainstVersionRequest;
use function FlixTech\SchemaRegistryApi\Requests\defaultCompatibilityLevelRequest;
use function FlixTech\SchemaRegistryApi\Requests\deleteSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\registerNewSchemaVersionWithSubjectRequest;
use function FlixTech\SchemaRegistryApi\Requests\schemaRequest;
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
    public function allSubjectsRequest(): array
    {
        return $this->parseJsonResponse($this->client->send(allSubjectsRequest()));
    }

    /**
     * @param string $subjectName
     * @return array
     * @throws GuzzleException
     */
    public function allSubjectVersionsRequest(string $subjectName): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                allSubjectVersionsRequest($subjectName)
            )
        );
    }

    /**
     * @param string $subjectName
     * @param string $versionId
     * @return array
     * @throws GuzzleException
     */
    public function singleSubjectVersionRequest(string $subjectName, string $versionId): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                singleSubjectVersionRequest($subjectName, $versionId)
            )
        );
    }

    /**
     * @param string $schema
     * @param string $subjectName
     * @return array
     * @throws GuzzleException
     */
    public function registerNewSchemaVersionWithSubjectRequest(string $schema, string $subjectName): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                registerNewSchemaVersionWithSubjectRequest($schema, $subjectName)
            )
        );
    }

    /**
     * @param string $schema
     * @param string $subjectName
     * @param string $versionId
     * @return array
     * @throws GuzzleException
     */
    public function checkSchemaCompatibilityAgainstVersionRequest(
        string $schema,
        string $subjectName,
        string $versionId
    ): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                checkSchemaCompatibilityAgainstVersionRequest($schema, $subjectName, $versionId)
            )
        );
    }

    /**
     * @param string $subjectName
     * @param string $schema
     * @return array
     * @throws GuzzleException
     */
    public function checkIfSubjectHasSchemaRegisteredRequest(string $subjectName, string $schema): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                checkIfSubjectHasSchemaRegisteredRequest($subjectName, $schema)
            ));
    }

    /**
     * @param string $id
     * @return array
     * @throws GuzzleException
     */
    public function schemaRequest(string $id): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                schemaRequest($id)
            )
        );
    }

    /**
     * @param string $id
     * @return array
     * @throws GuzzleException
     */
    public function deleteSubjectRequest(string $id): array {
        return $this->parseJsonResponse(
            $this->client->send(
                deleteSubjectRequest($id)
            )
        );
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function defaultCompatibilityLevelRequest(): array {
        return $this->parseJsonResponse(
            $this->client->send(
                defaultCompatibilityLevelRequest()
            )
        );
    }

    /**
     * @param string $subjectName
     * @return array
     * @throws GuzzleException
     */
    public function subjectCompatibilityLevelRequest(string $subjectName): array
    {
        return $this->parseJsonResponse(
            $this->client->send(
                subjectCompatibilityLevelRequest($subjectName)
            )
        );
    }
}