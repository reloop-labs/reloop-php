<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\ReloopClient;

class ContactsService
{
    public ContactGroupsService $groups;
    public ContactChannelsService $channels;

    public function __construct(private ReloopClient $client)
    {
        $this->groups = new ContactGroupsService($client);
        $this->channels = new ContactChannelsService($client);
    }

    public function create(array $params): array
    {
        return $this->client->request('POST', '/api/contacts/create', [
            RequestOptions::JSON => $params,
        ]);
    }

    public function get(string $contactId): array
    {
        return $this->client->request('GET', "/api/contacts/retrieve/{$contactId}");
    }

    public function list(array $params = []): array
    {
        if (isset($params['groupId'])) {
            $groupId = $params['groupId'];
            unset($params['groupId']);

            return $this->groups->listContacts($groupId, $params);
        }

        return $this->client->request('GET', '/api/contacts/list', [
            RequestOptions::QUERY => $params,
        ]);
    }

    public function update(string $contactId, array $params): array
    {
        return $this->client->request('PATCH', "/api/contacts/{$contactId}", [
            RequestOptions::JSON => $params,
        ]);
    }

    public function delete(string $contactId): array
    {
        return $this->client->request('DELETE', "/api/contacts/{$contactId}");
    }

    public function createProperty(array $params): array
    {
        return $this->client->request('POST', '/api/contacts/v1/properties/create', [
            RequestOptions::JSON => $params,
        ]);
    }

    public function listProperties(array $params = []): array
    {
        return $this->client->request('GET', '/api/contacts/v1/properties/list', [
            RequestOptions::QUERY => $params,
        ]);
    }

    public function updateProperty(string $propertyId, array $params): array
    {
        return $this->client->request(
            'PATCH',
            "/api/contacts/v1/properties/{$propertyId}",
            [RequestOptions::JSON => $params],
        );
    }

    public function deleteProperty(string $propertyId): array
    {
        return $this->client->request(
            'DELETE',
            "/api/contacts/v1/properties/{$propertyId}",
        );
    }

    public function createGroup(array $params): array
    {
        return $this->client->request('POST', '/api/contacts/v1/groups/create', [
            RequestOptions::JSON => $params,
        ]);
    }

    public function listGroups(array $params = []): array
    {
        return $this->client->request('GET', '/api/contacts/v1/groups/list', [
            RequestOptions::QUERY => $params,
        ]);
    }

    public function getGroup(string $groupId): array
    {
        return $this->client->request('GET', "/api/contacts/v1/groups/{$groupId}");
    }

    public function updateGroup(string $groupId, array $params): array
    {
        return $this->client->request('PATCH', "/api/contacts/v1/groups/{$groupId}", [
            RequestOptions::JSON => $params,
        ]);
    }

    public function deleteGroup(string $groupId): array
    {
        return $this->client->request('DELETE', "/api/contacts/v1/groups/{$groupId}");
    }
}
