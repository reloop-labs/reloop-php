<?php

namespace Reloop;

use Reloop\Services\ApiKeyService;
use Reloop\Services\ContactsService;

class Reloop
{
    public ApiKeyService $apiKeys;
    public ContactsService $contacts;
    private ReloopClient $client;

    /**
     * Create a new Reloop client with the given API key.
     */
    public static function client(string $apiKey, string $baseUrl = 'https://reloop.sh'): self
    {
        return new self($apiKey, $baseUrl);
    }

    /**
     * Initialize the Reloop SDK
     *
     * @param string $apiKey Your API key
     * @param string $baseUrl Optional override for the base URL
     */
    public function __construct(string $apiKey, string $baseUrl = 'https://reloop.sh')
    {
        $this->client = new ReloopClient($apiKey, $baseUrl);
        $this->apiKeys = new ApiKeyService($this->client);
        $this->contacts = new ContactsService($this->client);
    }
}
