<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\ReloopClient;

class ContactGroupsService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function addContact(string $groupId, array $params): array
    {
        return $this->client->request('POST', "/api/contacts/group/{$groupId}", [
            RequestOptions::JSON => $params,
        ]);
    }

    public function removeContact(string $groupId, array $params): array
    {
        return $this->client->request('DELETE', "/api/contacts/group/{$groupId}", [
            RequestOptions::JSON => $params,
        ]);
    }

    public function listContacts(string $groupId, array $params = []): array
    {
        return $this->client->request(
            'GET',
            "/api/contacts/v1/groups/{$groupId}/contacts",
            [RequestOptions::QUERY => $params],
        );
    }
}
