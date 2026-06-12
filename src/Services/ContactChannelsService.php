<?php

namespace Reloop\Services;

use GuzzleHttp\RequestOptions;
use Reloop\ChannelList;
use Reloop\ContactChannel;
use Reloop\ReloopClient;
use Reloop\Support\Parameters;
use Reloop\Support\ResourceFactory;

class ContactChannelsService
{
    public function __construct(private ReloopClient $client)
    {
    }

    public function create(array $parameters): ContactChannel
    {
        $data = $this->client->request('POST', '/api/contacts/v1/channels/create', [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contactChannel($data);
    }

    public function list(array $options = []): ChannelList
    {
        $data = $this->client->request('GET', '/api/contacts/v1/channels/list', [
            RequestOptions::QUERY => Parameters::forQuery($options),
        ]);

        return ResourceFactory::channelList($data);
    }

    public function get(string $channelId): ContactChannel
    {
        $data = $this->client->request('GET', "/api/contacts/v1/channels/{$channelId}");

        return ResourceFactory::contactChannel($data);
    }

    public function update(string $channelId, array $parameters): ContactChannel
    {
        $data = $this->client->request('PATCH', "/api/contacts/v1/channels/{$channelId}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contactChannel($data);
    }

    public function delete(string $channelId): ContactChannel
    {
        $data = $this->client->request('DELETE', "/api/contacts/v1/channels/{$channelId}");

        return ResourceFactory::contactChannel($data);
    }

    public function addContact(string $channelId, array $parameters): ContactChannel
    {
        $data = $this->client->request('POST', "/api/contacts/channel/{$channelId}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contactChannel($data);
    }

    public function updateSubscription(string $channelId, array $parameters): ContactChannel
    {
        $data = $this->client->request('PATCH', "/api/contacts/channel/{$channelId}", [
            RequestOptions::JSON => Parameters::forRequest($parameters),
        ]);

        return ResourceFactory::contactChannel($data);
    }
}
