<?php

namespace Reloop\Services;

use Reloop\ReloopClient;
use Reloop\Support\Validators;

class DomainService
{
    private const DOMAIN_V1 = '/api/domain/v1';

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{
     *   domain: string,
     *   click_tracking?: bool,
     *   open_tracking?: bool,
     *   tls?: string,
     *   sending_email?: bool,
     *   receiving_email?: bool
     * } $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        $domain = Validators::requireNonEmptyString($params['domain'] ?? null, 'domain');
        $body = array_filter([
            'domain' => $domain,
            'click_tracking' => $params['click_tracking'] ?? null,
            'open_tracking' => $params['open_tracking'] ?? null,
            'tls' => $params['tls'] ?? null,
            'sending_email' => $params['sending_email'] ?? null,
            'receiving_email' => $params['receiving_email'] ?? null,
        ], static fn ($v) => $v !== null);

        return $this->client->request('POST', self::DOMAIN_V1 . '/create', $body) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int, q?: string, status?: string}|null $params
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
            if (isset($params['q'])) {
                $query['q'] = $params['q'];
            }
            if (isset($params['status'])) {
                $query['status'] = $params['status'];
            }
        }

        return $this->client->request('GET', self::DOMAIN_V1 . '/list', null, $query) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $domainId): array
    {
        $id = Validators::requireNonEmptyString($domainId, 'domainId');

        return $this->client->request('GET', self::DOMAIN_V1 . '/' . $id) ?? [];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function update(string $domainId, array $params = []): array
    {
        $id = Validators::requireNonEmptyString($domainId, 'domainId');
        $body = array_filter([
            'click_tracking' => $params['click_tracking'] ?? null,
            'open_tracking' => $params['open_tracking'] ?? null,
            'sending_email' => $params['sending_email'] ?? null,
            'receiving_email' => $params['receiving_email'] ?? null,
            'tls' => $params['tls'] ?? null,
        ], static fn ($v) => $v !== null);

        return $this->client->request('PATCH', self::DOMAIN_V1 . '/' . $id, $body) ?? [];
    }

    public function delete(string $domainId): void
    {
        $id = Validators::requireNonEmptyString($domainId, 'domainId');
        $this->client->request('DELETE', self::DOMAIN_V1 . '/' . $id);
    }

    /** @return array<string, mixed> */
    public function verify(string $domainId): array
    {
        $id = Validators::requireNonEmptyString($domainId, 'domainId');

        return $this->client->request('POST', self::DOMAIN_V1 . '/verify/' . $id, []) ?? [];
    }
}
