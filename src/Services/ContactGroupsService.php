<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\Contact;
use Reloop\ContactGroup;
use Reloop\ReloopClient;
use Reloop\Support\Parameters;
use Reloop\Support\ResourceFactory;

class ContactGroupsService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function addContact(string $groupId, array $parameters): Contact
    {
        $data = $this->client->request('POST', "/api/contacts/group/{$groupId}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contact($data);
    }

    public function removeContact(string $groupId, array $parameters): Contact
    {
        $data = $this->client->request('DELETE', "/api/contacts/group/{$groupId}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contact($data);
    }

    public function listContacts(string $groupId, array $options = []): ContactGroup
    {
        $data = $this->client->request(
            'GET',
            "/api/contacts/v1/groups/{$groupId}/contacts",
            [RequestOptions::QUERY => Parameters::forQuery($options)],
        );

        return ResourceFactory::contactGroup($data);
    }
}
