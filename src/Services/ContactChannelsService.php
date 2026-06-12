<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\ReloopClient;

class ContactChannelsService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function create(array $params): array
    {
        return $this->client->request('POST', '/api/contacts/v1/channels/create', [
            RequestOptions::JSON => $params,
        ]);
    }

    public function list(array $params = []): array
    {
        return $this->client->request('GET', '/api/contacts/v1/channels/list', [
            RequestOptions::QUERY => $params,
        ]);
    }

    public function get(string $channelId): array
    {
        return $this->client->request('GET', "/api/contacts/v1/channels/{$channelId}");
    }

    public function update(string $channelId, array $params): array
    {
        return $this->client->request('PATCH', "/api/contacts/v1/channels/{$channelId}", [
            RequestOptions::JSON => $params,
        ]);
    }

    public function delete(string $channelId): array
    {
        return $this->client->request('DELETE', "/api/contacts/v1/channels/{$channelId}");
    }

    public function addContact(string $channelId, array $params): array
    {
        return $this->client->request('POST', "/api/contacts/channel/{$channelId}", [
            RequestOptions::JSON => $params,
        ]);
    }

    public function updateSubscription(string $channelId, array $params): array
    {
        return $this->client->request('PATCH', "/api/contacts/channel/{$channelId}", [
            RequestOptions::JSON => $params,
        ]);
    }
}
