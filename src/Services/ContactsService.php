<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\Contact;
use Reloop\ContactGroup;
use Reloop\ContactList;
use Reloop\ContactProperty;
use Reloop\GroupList;
use Reloop\PropertyList;
use Reloop\ReloopClient;
use Reloop\Support\Parameters;
use Reloop\Support\ResourceFactory;

class ContactsService
{
    public ContactGroupsService $groups;
    public ContactChannelsService $channels;

    public function __construct(private ReloopClient $client)
    {
        $this->groups = new ContactGroupsService($client);
        $this->channels = new ContactChannelsService($client);
    }

    public function create(array $parameters): Contact
    {
        $data = $this->client->request('POST', '/api/contacts/create', [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contact($data);
    }

    public function get(string $contactId): Contact
    {
        $data = $this->client->request('GET', "/api/contacts/retrieve/{$contactId}");

        return ResourceFactory::contact($data);
    }

    public function list(array $options = []): ContactList|ContactGroup
    {
        $query = Parameters::forQuery($options);

        if (isset($query['groupId'])) {
            $groupId = $query['groupId'];
            unset($query['groupId']);

            return $this->groups->listContacts($groupId, $query);
        }

        $data = $this->client->request('GET', '/api/contacts/list', [
            RequestOptions::QUERY => $query,
        ]);

        return ResourceFactory::contactList($data);
    }

    public function update(string $contactId, array $parameters): Contact
    {
        $data = $this->client->request('PATCH', "/api/contacts/{$contactId}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contact($data);
    }

    public function delete(string $contactId): Contact
    {
        $data = $this->client->request('DELETE', "/api/contacts/{$contactId}");

        return ResourceFactory::contact($data);
    }

    public function createProperty(array $parameters): ContactProperty
    {
        $data = $this->client->request('POST', '/api/contacts/v1/properties/create', [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contactProperty($data);
    }

    public function listProperties(array $options = []): PropertyList
    {
        $data = $this->client->request('GET', '/api/contacts/v1/properties/list', [
            RequestOptions::QUERY => Parameters::forQuery($options),
        ]);

        return ResourceFactory::propertyList($data);
    }

    public function updateProperty(string $propertyId, array $parameters): ContactProperty
    {
        $data = $this->client->request(
            'PATCH',
            "/api/contacts/v1/properties/{$propertyId}",
            [RequestOptions::JSON => Parameters::forRequest($parameters)],
        );

        return ResourceFactory::contactProperty($data);
    }

    public function deleteProperty(string $propertyId): ContactProperty
    {
        $data = $this->client->request(
            'DELETE',
            "/api/contacts/v1/properties/{$propertyId}",
        );

        return ResourceFactory::contactProperty($data);
    }

    public function createGroup(array $parameters): ContactGroup
    {
        $data = $this->client->request('POST', '/api/contacts/v1/groups/create', [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contactGroup($data);
    }

    public function listGroups(array $options = []): GroupList
    {
        $data = $this->client->request('GET', '/api/contacts/v1/groups/list', [
            RequestOptions::QUERY => Parameters::forQuery($options),
        ]);

        return ResourceFactory::groupList($data);
    }

    public function getGroup(string $groupId): ContactGroup
    {
        $data = $this->client->request('GET', "/api/contacts/v1/groups/{$groupId}");

        return ResourceFactory::contactGroup($data);
    }

    public function updateGroup(string $groupId, array $parameters): ContactGroup
    {
        $data = $this->client->request('PATCH', "/api/contacts/v1/groups/{$groupId}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contactGroup($data);
    }

    public function deleteGroup(string $groupId): ContactGroup
    {
        $data = $this->client->request('DELETE', "/api/contacts/v1/groups/{$groupId}");

        return ResourceFactory::contactGroup($data);
    }
}
