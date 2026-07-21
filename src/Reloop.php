<?php

namespace Reloop;

use GuzzleHttp\Client;
use Reloop\Services\ApiKeyService;
use Reloop\Services\ContactsService;
use Reloop\Services\DomainService;
use Reloop\Services\InboxService;
use Reloop\Services\MailService;
use Reloop\Services\WebhookService;

class Reloop
{
    public ApiKeyService $apiKey;
    public ContactsService $contacts;
    public DomainService $domain;
    public MailService $mail;
    public WebhookService $webhook;
    public InboxService $inbox;

    private ReloopClient $client;

    public static function client(string $apiKey, string $baseUrl = 'https://reloop.sh', ?Client $httpClient = null): self
    {
        return new self($apiKey, $baseUrl, $httpClient);
    }

    private function __construct(string $apiKey, string $baseUrl = 'https://reloop.sh', ?Client $httpClient = null)
    {
        $this->client = new ReloopClient($apiKey, $baseUrl, $httpClient);
        $this->apiKey = new ApiKeyService($this->client);
        $this->contacts = new ContactsService($this->client);
        $this->domain = new DomainService($this->client);
        $this->mail = new MailService($this->client);
        $this->webhook = new WebhookService($this->client);
        $this->inbox = new InboxService($this->client);
    }

    public function getClient(): ReloopClient
    {
        return $this->client;
    }
}
