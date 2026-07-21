<?php

namespace Reloop\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Reloop\Reloop;

final class TestClientFactory
{
    /** @var list<array{request: \Psr\Http\Message\RequestInterface, options: array}> */
    public array $history = [];

    public MockHandler $mock;

    public function __construct()
    {
        $this->mock = new MockHandler();
        $this->history = [];
    }

    public function client(string $apiKey = 'rl_test'): Reloop
    {
        $history = Middleware::history($this->history);
        $stack = HandlerStack::create($this->mock);
        $stack->push($history);

        $http = new Client([
            'handler' => $stack,
            'base_uri' => 'https://reloop.test/',
            'http_errors' => false,
        ]);

        return Reloop::client($apiKey, 'https://reloop.test', $http);
    }

    public function respond(int $status, array|string $body = []): void
    {
        $json = is_string($body) ? $body : json_encode($body, JSON_THROW_ON_ERROR);
        $this->mock->append(new Response($status, ['Content-Type' => 'application/json'], $json));
    }

    public function hits(): int
    {
        return count($this->history);
    }

    public function lastRequest(): ?\Psr\Http\Message\RequestInterface
    {
        if ($this->history === []) {
            return null;
        }

        return $this->history[array_key_last($this->history)]['request'];
    }
}
