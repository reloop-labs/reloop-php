<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\ApiKey;
use Reloop\ApiKeyList;
use Reloop\ReloopClient;
use Reloop\Support\Parameters;
use Reloop\Support\ResourceFactory;

class ApiKeyService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function create(array $parameters): ApiKey
    {
        $data = $this->client->request('POST', '/api/api-key/v1/', [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::apiKey($data);
    }

    public function list(array $options = []): ApiKeyList
    {
        $data = $this->client->request('GET', '/api/api-key/v1/', [
            RequestOptions::QUERY => Parameters::forQuery($options),
        ]);

        return ResourceFactory::apiKeyList($data);
    }

    public function get(string $id): ApiKey
    {
        $data = $this->client->request('GET', "/api/api-key/v1/{$id}");

        return ResourceFactory::apiKey($data);
    }

    public function update(string $id, array $parameters): ApiKey
    {
        $data = $this->client->request('PATCH', "/api/api-key/v1/{$id}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::apiKey($data);
    }

    public function delete(string $id): ApiKey
    {
        $data = $this->client->request('DELETE', "/api/api-key/v1/{$id}");

        return ResourceFactory::apiKey($data);
    }

    public function rotate(string $id): ApiKey
    {
        $data = $this->client->request('POST', "/api/api-key/v1/rotate/{$id}");

        return ResourceFactory::apiKey($data);
    }

    public function enable(string $id): ApiKey
    {
        $data = $this->client->request('POST', "/api/api-key/v1/enable/{$id}");

        return ResourceFactory::apiKey($data);
    }

    public function disable(string $id): ApiKey
    {
        $data = $this->client->request('POST', "/api/api-key/v1/disable/{$id}");

        return ResourceFactory::apiKey($data);
    }

    public function pause(string $id): ApiKey
    {
        return $this->disable($id);
    }
}
