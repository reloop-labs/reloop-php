<?php

namespace Reloop;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Reloop\Exceptions\ReloopApiException;

class ReloopClient
{
    private string $apiKey;
    private string $baseUrl;
    private Client $httpClient;

    public function __construct(string $apiKey, string $baseUrl = 'https://reloop.sh', ?Client $httpClient = null)
    {
        $trimmed = trim($apiKey);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Reloop SDK requires an APIKey');
        }

        $this->apiKey = $trimmed;
        $base = rtrim($baseUrl === '' ? 'https://reloop.sh' : $baseUrl, '/');
        $this->baseUrl = $base === '' ? 'https://reloop.sh' : $base;

        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => $this->baseUrl . '/',
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'http_errors' => false,
            'timeout' => 30,
        ]);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, scalar|null>|null $query
     * @return array<string, mixed>|null
     */
    public function request(string $method, string $path, ?array $body = null, ?array $query = null): ?array
    {
        $options = [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if ($body !== null) {
            $options[RequestOptions::JSON] = $body;
        }
        if ($query !== null) {
            $filtered = array_filter(
                $query,
                static fn ($v) => $v !== null,
            );
            if ($filtered !== []) {
                $options[RequestOptions::QUERY] = $filtered;
            }
        }

        try {
            $response = $this->httpClient->request($method, ltrim($path, '/'), $options);
            $statusCode = $response->getStatusCode();
            $raw = (string) $response->getBody();

            if ($statusCode >= 400) {
                $errorBody = json_decode($raw, true);
                if (!is_array($errorBody)) {
                    $errorBody = ['message' => $raw !== '' ? $raw : $response->getReasonPhrase()];
                }
                throw new ReloopApiException($statusCode, (string) $statusCode, $errorBody);
            }

            if ($statusCode === 204 || $raw === '') {
                return null;
            }

            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        } catch (ReloopApiException $e) {
            throw $e;
        } catch (GuzzleException $e) {
            throw new ReloopApiException(0, 'Network Error', ['message' => $e->getMessage()], $e);
        }
    }
}
