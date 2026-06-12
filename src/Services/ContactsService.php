<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\Dto\Dto;
use Reloop\Dto\Request\CreateContactParams;
use Reloop\Dto\Request\CreateGroupParams;
use Reloop\Dto\Request\CreatePropertyParams;
use Reloop\Dto\Request\ListContactsParams;
use Reloop\Dto\Request\ListGroupsParams;
use Reloop\Dto\Request\ListPropertiesParams;
use Reloop\Dto\Request\UpdateContactParams;
use Reloop\Dto\Request\UpdateGroupParams;
use Reloop\Dto\Request\UpdatePropertyParams;
use Reloop\Dto\Response\Contact;
use Reloop\Dto\Response\ContactGroup;
use Reloop\Dto\Response\ContactGroupResponse;
use Reloop\Dto\Response\ContactListResponse;
use Reloop\Dto\Response\ContactPropertyResponse;
use Reloop\Dto\Response\ContactResponse;
use Reloop\Dto\Response\DeleteContactResponse;
use Reloop\Dto\Response\DeleteGroupResponse;
use Reloop\Dto\Response\DeletePropertyResponse;
use Reloop\Dto\Response\GroupContactListResponse;
use Reloop\Dto\Response\GroupListResponse;
use Reloop\Dto\Response\PropertyListResponse;
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

    public function create(CreateContactParams|array $params): ContactResponse
    {
        $data = $this->client->request('POST', '/api/contacts/create', [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactResponse::fromArray($data);
    }

    public function get(string $contactId): Contact
    {
        $data = $this->client->request('GET', "/api/contacts/retrieve/{$contactId}");

        return Contact::fromArray($data);
    }

    public function list(ListContactsParams|array $params = []): ContactListResponse|GroupContactListResponse
    {
        $payload = Dto::query($params);

        if (isset($payload['groupId'])) {
            $groupId = $payload['groupId'];
            unset($payload['groupId']);

            return $this->groups->listContacts($groupId, $payload);
        }

        $data = $this->client->request('GET', '/api/contacts/list', [
            RequestOptions::QUERY => $payload,
        ]);

        return ContactListResponse::fromArray($data);
    }

    public function update(string $contactId, UpdateContactParams|array $params): ContactResponse
    {
        $data = $this->client->request('PATCH', "/api/contacts/{$contactId}", [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactResponse::fromArray($data);
    }

    public function delete(string $contactId): DeleteContactResponse
    {
        $data = $this->client->request('DELETE', "/api/contacts/{$contactId}");

        return DeleteContactResponse::fromArray($data);
    }

    public function createProperty(CreatePropertyParams|array $params): ContactPropertyResponse
    {
        $data = $this->client->request('POST', '/api/contacts/v1/properties/create', [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactPropertyResponse::fromArray($data);
    }

    public function listProperties(ListPropertiesParams|array $params = []): PropertyListResponse
    {
        $data = $this->client->request('GET', '/api/contacts/v1/properties/list', [
            RequestOptions::QUERY => Dto::query($params),
        ]);

        return PropertyListResponse::fromArray($data);
    }

    public function updateProperty(
        string $propertyId,
        UpdatePropertyParams|array $params,
    ): ContactPropertyResponse {
        $data = $this->client->request(
            'PATCH',
            "/api/contacts/v1/properties/{$propertyId}",
            [RequestOptions::JSON => Dto::body($params)],
        );

        return ContactPropertyResponse::fromArray($data);
    }

    public function deleteProperty(string $propertyId): DeletePropertyResponse
    {
        $data = $this->client->request(
            'DELETE',
            "/api/contacts/v1/properties/{$propertyId}",
        );

        return DeletePropertyResponse::fromArray($data);
    }

    public function createGroup(CreateGroupParams|array $params): ContactGroupResponse
    {
        $data = $this->client->request('POST', '/api/contacts/v1/groups/create', [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactGroupResponse::fromArray($data);
    }

    public function listGroups(ListGroupsParams|array $params = []): GroupListResponse
    {
        $data = $this->client->request('GET', '/api/contacts/v1/groups/list', [
            RequestOptions::QUERY => Dto::query($params),
        ]);

        return GroupListResponse::fromArray($data);
    }

    public function getGroup(string $groupId): ContactGroup
    {
        $data = $this->client->request('GET', "/api/contacts/v1/groups/{$groupId}");

        return ContactGroup::fromArray($data);
    }

    public function updateGroup(string $groupId, UpdateGroupParams|array $params): ContactGroupResponse
    {
        $data = $this->client->request('PATCH', "/api/contacts/v1/groups/{$groupId}", [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactGroupResponse::fromArray($data);
    }

    public function deleteGroup(string $groupId): DeleteGroupResponse
    {
        $data = $this->client->request('DELETE', "/api/contacts/v1/groups/{$groupId}");

        return DeleteGroupResponse::fromArray($data);
    }
}
