<?php

namespace Reloop\Services;

use Reloop\ReloopClient;
use GuzzleHttp\RequestOptions;

class ApiKeyService
{
    private ReloopClient $client;

    public function __construct(ReloopClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new API key
     *
     * @param array $params ['name' => string]
     * @return array
     */
    public function create(array $params): array
    {
        return $this->client->request('POST', '/api/api-key/v1/', [
            RequestOptions::JSON => $params,
        ]);
    }

    /**
     * List API keys
     *
     * @param array $params ['page' => int, 'limit' => int, 'enabled' => bool, 'userId' => string, 'q' => string]
     * @return array
     */
    public function list(array $params = []): array
    {
        return $this->client->request('GET', '/api/api-key/v1/', [
            RequestOptions::QUERY => $params,
        ]);
    }

    /**
     * Get an API key by ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id): array
    {
        return $this->client->request('GET', "/api/api-key/v1/{$id}");
    }

    /**
     * Update an API key
     *
     * @param string $id
     * @param array $params ['name' => string]
     * @return array
     */
    public function update(string $id, array $params): array
    {
        return $this->client->request('PATCH', "/api/api-key/v1/{$id}", [
            RequestOptions::JSON => $params,
        ]);
    }

    /**
     * Delete an API key
     *
     * @param string $id
     * @return array
     */
    public function delete(string $id): array
    {
        return $this->client->request('DELETE', "/api/api-key/v1/{$id}");
    }

    /**
     * Rotate an API key
     *
     * @param string $id
     * @return array
     */
    public function rotate(string $id): array
    {
        return $this->client->request('POST', "/api/api-key/v1/rotate/{$id}");
    }

    /**
     * Enable an API key
     *
     * @param string $id
     * @return array
     */
    public function enable(string $id): array
    {
        return $this->client->request('POST', "/api/api-key/v1/enable/{$id}");
    }

    /**
     * Disable an API key
     *
     * @param string $id
     * @return array
     */
    public function disable(string $id): array
    {
        return $this->client->request('POST', "/api/api-key/v1/disable/{$id}");
    }
}
