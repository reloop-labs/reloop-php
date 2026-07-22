<?php

namespace Reloop\Services;

use Reloop\ReloopClient;
use Reloop\Support\Validators;

class ApiKeyService
{
    private const API_KEY_V1 = '/api/api-key/v1';

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{name?: string} $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        $name = Validators::requireApiKeyName($params['name'] ?? null, 'name');

        return $this->client->request('POST', self::API_KEY_V1 . '/', ['name' => $name]) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int, enabled?: bool, userId?: string, q?: string}|null $params
     * @return array<string, mixed>
     */
    public function list(?array $params = null): array
    {
        $query = [];
        if ($params !== null) {
            if (isset($params['page'])) {
                Validators::requirePage((int) $params['page'], 'page');
                $query['page'] = (int) $params['page'];
            }
            if (isset($params['limit'])) {
                Validators::requireLimit((int) $params['limit'], 1, 100, 'limit');
                $query['limit'] = (int) $params['limit'];
            }
            if (array_key_exists('enabled', $params)) {
                $query['enabled'] = $params['enabled'] ? 'true' : 'false';
            }
            if (isset($params['userId'])) {
                $query['userId'] = $params['userId'];
            }
            if (isset($params['q'])) {
                $query['q'] = $params['q'];
            }
        }

        return $this->client->request('GET', self::API_KEY_V1 . '/', null, $query) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $apiKeyId): array
    {
        $id = Validators::requireApiKeyId($apiKeyId, 'apiKeyId');

        return $this->client->request('GET', self::API_KEY_V1 . '/' . $id) ?? [];
    }

    /**
     * @param array{name?: string} $params
     * @return array<string, mixed>
     */
    public function update(string $apiKeyId, array $params): array
    {
        $id = Validators::requireApiKeyId($apiKeyId, 'apiKeyId');
        $name = Validators::requireApiKeyName($params['name'] ?? null, 'name');

        return $this->client->request('PATCH', self::API_KEY_V1 . '/' . $id, ['name' => $name]) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $apiKeyId): array
    {
        $id = Validators::requireApiKeyId($apiKeyId, 'apiKeyId');

        return $this->client->request('DELETE', self::API_KEY_V1 . '/' . $id) ?? [];
    }

    /** @return array<string, mixed> */
    public function rotate(string $apiKeyId): array
    {
        $id = Validators::requireApiKeyId($apiKeyId, 'apiKeyId');

        return $this->client->request('POST', self::API_KEY_V1 . '/rotate/' . $id, []) ?? [];
    }

    /** @return array<string, mixed> */
    public function enable(string $apiKeyId): array
    {
        $id = Validators::requireApiKeyId($apiKeyId, 'apiKeyId');

        return $this->client->request('POST', self::API_KEY_V1 . '/enable/' . $id, []) ?? [];
    }

    /** @return array<string, mixed> */
    public function disable(string $apiKeyId): array
    {
        $id = Validators::requireApiKeyId($apiKeyId, 'apiKeyId');

        return $this->client->request('POST', self::API_KEY_V1 . '/disable/' . $id, []) ?? [];
    }
}
