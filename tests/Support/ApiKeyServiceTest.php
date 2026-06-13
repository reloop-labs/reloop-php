<?php

namespace Reloop\Tests\Support;

use PHPUnit\Framework\TestCase;
use Reloop\ReloopClient;
use Reloop\Services\ApiKeyService;

final class ApiKeyServiceTest extends TestCase
{
    public function testCreateUsesApiPrefix(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/api-key/v1/',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['name'] ?? null) === 'Production Key';
                }),
            )
            ->willReturn(['id' => 'key_1']);

        $service = new ApiKeyService($client);
        $result = $service->create(['name' => 'Production Key']);

        $this->assertSame('key_1', $result->id);
    }

    public function testPauseDelegatesToDisable(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/api/api-key/v1/disable/key_1')
            ->willReturn(['id' => 'key_1', 'enabled' => false]);

        $service = new ApiKeyService($client);
        $service->pause('key_1');
    }

    public function testListBuildsQueryParams(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api/api-key/v1/',
                $this->callback(static function (array $options): bool {
                    return ($options['query']['page'] ?? null) === 2
                        && ($options['query']['limit'] ?? null) === 5
                        && ($options['query']['enabled'] ?? null) === true
                        && ($options['query']['q'] ?? null) === 'prod';
                }),
            )
            ->willReturn([
                'object' => 'api_key',
                'apiKeys' => [],
                'total' => 0,
                'page' => 2,
                'limit' => 5,
                'event' => 'evt_1',
            ]);

        $service = new ApiKeyService($client);
        $service->list(['page' => 2, 'limit' => 5, 'enabled' => true, 'q' => 'prod']);
    }

    public function testRotateUsesRotateRoute(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with('POST', '/api/api-key/v1/rotate/key_1')
            ->willReturn(['id' => 'key_1', 'key' => 'rl_live_rotated']);

        $service = new ApiKeyService($client);
        $service->rotate('key_1');
    }
}
