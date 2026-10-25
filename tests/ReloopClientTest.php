<?php

namespace Reloop\Tests;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Reloop;
use Reloop\Tests\Support\TestClientFactory;
use Reloop\Version;

class ReloopClientTest extends TestCase
{
    public function testRequiresApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Reloop::client('');
    }

    public function testRequiresWhitespaceApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Reloop::client('   ');
    }

    public function testWiresServicesAndVersion(): void
    {
        $reloop = Reloop::client('rl_test');
        $this->assertSame('https://reloop.sh', $reloop->getClient()->getBaseUrl());
        $this->assertNotNull($reloop->apiKey);
        $this->assertNotNull($reloop->mail);
        $this->assertNotNull($reloop->domain);
        $this->assertNotNull($reloop->contacts);
        $this->assertNotNull($reloop->webhook);
        $this->assertNotNull($reloop->inbox);
        $this->assertSame('2.0.1', Version::VERSION);
    }

    public function testAuthHeaderAndApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(401, ['message' => 'bad key']);
        $reloop = $factory->client();

        try {
            $reloop->getClient()->request('GET', '/v1/test');
            $this->fail('expected ReloopApiException');
        } catch (ReloopApiException $e) {
            $this->assertSame(401, $e->status);
            $this->assertSame('Unauthorized', $e->statusText);
            $this->assertSame('bad key', $e->body['message']);
            $this->assertStringNotContainsString('401 401', $e->getMessage());
        }

        $req = $factory->lastRequest();
        $this->assertNotNull($req);
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
    }
}
