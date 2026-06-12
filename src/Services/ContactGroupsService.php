<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\Dto\Dto;
use Reloop\Dto\Request\AddContactToGroupParams;
use Reloop\Dto\Request\ListContactsParams;
use Reloop\Dto\Request\RemoveContactFromGroupParams;
use Reloop\Dto\Response\ContactMutationResponse;
use Reloop\Dto\Response\GroupContactListResponse;
use Reloop\ReloopClient;

class ContactGroupsService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function addContact(string $groupId, AddContactToGroupParams|array $params): ContactMutationResponse
    {
        $data = $this->client->request('POST', "/api/contacts/group/{$groupId}", [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactMutationResponse::fromArray($data);
    }

    public function removeContact(string $groupId, RemoveContactFromGroupParams|array $params): ContactMutationResponse
    {
        $data = $this->client->request('DELETE', "/api/contacts/group/{$groupId}", [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactMutationResponse::fromArray($data);
    }

    public function listContacts(
        string $groupId,
        ListContactsParams|array $params = [],
    ): GroupContactListResponse {
        $data = $this->client->request(
            'GET',
            "/api/contacts/v1/groups/{$groupId}/contacts",
            [RequestOptions::QUERY => Dto::query($params)],
        );

        return GroupContactListResponse::fromArray($data);
    }
}
