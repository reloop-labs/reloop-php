<?php

namespace Reloop;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

class ReloopClient
{
    private string $apiKey;
    private string $baseUrl;
    private Client $httpClient;

    public function __construct(string $apiKey, string $baseUrl = 'https://reloop.sh')
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('Reloop SDK requires an apiKey.');
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            // We handle exceptions manually for unified error tracking
            'http_errors' => false,
        ]);
    }

    /**
     * Perform an HTTP request
     *
     * @param string $method HTTP Method (GET, POST, etc.)
     * @param string $path Endpoint path
     * @param array $options Guzzle request options
     * @return array|null Parsed JSON response or null for 204
     * @throws \Exception
     */
    public function request(string $method, string $path, array $options = []): ?array
    {
        try {
            $response = $this->httpClient->request($method, $path, $options);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                $errorBody = json_decode($response->getBody()->getContents(), true) ?? [];
                $message = $errorBody['message'] ?? $response->getReasonPhrase();
                throw new \Exception("Reloop API Error ($statusCode): $message");
            }

            if ($statusCode === 204) {
                return null;
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new \Exception("Reloop Network Error: " . $e->getMessage(), 0, $e);
        }
    }
}
