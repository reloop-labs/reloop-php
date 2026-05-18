<?php

namespace Reloop;

use Reloop\Services\ApiKeyService;

class Reloop
{
    public ApiKeyService $apiKeys;
    private ReloopClient $client;

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
    }
}
