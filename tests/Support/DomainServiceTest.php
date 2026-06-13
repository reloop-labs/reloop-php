<?php

namespace Reloop\Tests\Support;

use PHPUnit\Framework\TestCase;
use Reloop\ReloopClient;
use Reloop\Services\DomainService;

final class DomainServiceTest extends TestCase
{
    public function testCreateUsesDomainCreateRouteWithSnakeCaseBody(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/domain/v1/create',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['domain'] ?? null) === 'send.example.com'
                        && ($options['json']['click_tracking'] ?? null) === true
                        && ($options['json']['custom_return_path'] ?? null) === 'inbound';
                }),
            )
            ->willReturn(['id' => 'dom_1', 'domain' => 'send.example.com']);

        $service = new DomainService($client);
        $result = $service->create([
            'domain' => 'send.example.com',
            'click_tracking' => true,
            'custom_return_path' => 'inbound',
        ]);

        $this->assertSame('dom_1', $result->id);
    }

    public function testGetNameserversUsesNameserversRoute(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with('GET', '/api/domain/v1/nameservers/dom_1')
            ->willReturn(['domainId' => 'dom_1', 'dnsProvider' => 'cloudflare']);

        $service = new DomainService($client);
        $result = $service->getNameservers('dom_1');

        $this->assertSame('dom_1', $result->domain_id);
        $this->assertSame('cloudflare', $result->dns_provider);
    }

    public function testForwardDnsUsesForwardDnsRoute(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/domain/v1/verify/dom_1/forward-dns',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['email'] ?? null) === 'admin@example.com';
                }),
            )
            ->willReturn(['success' => true]);

        $service = new DomainService($client);
        $result = $service->forwardDns('dom_1', ['email' => 'admin@example.com']);

        $this->assertTrue($result->success);
    }
}
