<?php

namespace Reloop;

use Reloop\Services\ApiKeyService;
use Reloop\Services\ContactsService;
use Reloop\Services\DomainService;

class Reloop
{
    public ApiKeyService $apiKeys;
    public ContactsService $contacts;
    public DomainService $domain;
    private ReloopClient $client;

    /**
     * Create a new Reloop client with the given API key.
     */
    public static function client(string $apiKey, string $baseUrl = 'https://reloop.sh'): self
    {
        return new self($apiKey, $baseUrl);
    }

    private function __construct(string $apiKey, string $baseUrl = 'https://reloop.sh')
    {
        $this->client = new ReloopClient($apiKey, $baseUrl);
        $this->apiKeys = new ApiKeyService($this->client);
        $this->contacts = new ContactsService($this->client);
        $this->domain = new DomainService($this->client);
    }
}
