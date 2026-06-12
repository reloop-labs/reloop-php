<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\Dto\Dto;
use Reloop\Dto\Request\AddContactToChannelParams;
use Reloop\Dto\Request\CreateChannelParams;
use Reloop\Dto\Request\ListChannelsParams;
use Reloop\Dto\Request\UpdateChannelParams;
use Reloop\Dto\Request\UpdateContactChannelParams;
use Reloop\Dto\Response\AddContactToChannelResponse;
use Reloop\Dto\Response\ChannelListResponse;
use Reloop\Dto\Response\ContactChannel;
use Reloop\Dto\Response\ContactChannelResponse;
use Reloop\Dto\Response\DeleteChannelResponse;
use Reloop\Dto\Response\UpdateContactChannelResponse;
use Reloop\ReloopClient;

class ContactChannelsService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function create(CreateChannelParams|array $params): ContactChannelResponse
    {
        $data = $this->client->request('POST', '/api/contacts/v1/channels/create', [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactChannelResponse::fromArray($data);
    }

    public function list(ListChannelsParams|array $params = []): ChannelListResponse
    {
        $data = $this->client->request('GET', '/api/contacts/v1/channels/list', [
            RequestOptions::QUERY => Dto::query($params),
        ]);

        return ChannelListResponse::fromArray($data);
    }

    public function get(string $channelId): ContactChannel
    {
        $data = $this->client->request('GET', "/api/contacts/v1/channels/{$channelId}");

        return ContactChannel::fromArray($data);
    }

    public function update(string $channelId, UpdateChannelParams|array $params): ContactChannelResponse
    {
        $data = $this->client->request('PATCH', "/api/contacts/v1/channels/{$channelId}", [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return ContactChannelResponse::fromArray($data);
    }

    public function delete(string $channelId): DeleteChannelResponse
    {
        $data = $this->client->request('DELETE', "/api/contacts/v1/channels/{$channelId}");

        return DeleteChannelResponse::fromArray($data);
    }

    public function addContact(string $channelId, AddContactToChannelParams|array $params): AddContactToChannelResponse
    {
        $data = $this->client->request('POST', "/api/contacts/channel/{$channelId}", [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return AddContactToChannelResponse::fromArray($data);
    }

    public function updateSubscription(
        string $channelId,
        UpdateContactChannelParams|array $params,
    ): UpdateContactChannelResponse {
        $data = $this->client->request('PATCH', "/api/contacts/channel/{$channelId}", [
            RequestOptions::JSON => Dto::body($params),
        ]);

        return UpdateContactChannelResponse::fromArray($data);
    }
}
