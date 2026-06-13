<?php

namespace Reloop\Tests\Support;

use PHPUnit\Framework\TestCase;
use Reloop\Support\Parameters;
use Reloop\Support\ResourceFactory;

final class DomainParametersTest extends TestCase
{
    public function testForSnakeRequestKeepsSnakeCase(): void
    {
        $payload = Parameters::forSnakeRequest([
            'domain' => 'send.example.com',
            'click_tracking' => true,
            'custom_return_path' => 'inbound',
            'ignored' => null,
        ]);

        $this->assertSame([
            'domain' => 'send.example.com',
            'click_tracking' => true,
            'custom_return_path' => 'inbound',
        ], $payload);
        $this->assertArrayNotHasKey('clickTracking', $payload);
    }

    public function testDomainResponseNormalizesToSnakeCase(): void
    {
        $domain = ResourceFactory::domain([
            'object' => 'domain',
            'id' => 'dom_1',
            'domain' => 'send.example.com',
            'status' => 'pending',
            'userVerifiedDomain' => false,
            'systemVerified' => false,
            'customReturnPath' => 'inbound',
            'trackingSubdomain' => 'tracking',
            'isClickTrackingEnabled' => true,
            'isOpenTrackingEnabled' => false,
            'tls' => 'opportunistic',
            'isTrackingDomain' => false,
            'isSendingEmailEnabled' => true,
            'isReceivingEmailEnabled' => true,
            'verificationFailedReason' => null,
            'dnsRecords' => [],
            'lastVerifiedAt' => null,
            'createdAt' => '2026-01-01T00:00:00.000Z',
            'updatedAt' => '2026-01-01T00:00:00.000Z',
        ]);

        $this->assertSame('dom_1', $domain->id);
        $this->assertTrue($domain->is_click_tracking_enabled);
        $this->assertSame([], $domain->dns_records);
    }

    public function testDomainNameserversResponseNormalizesToSnakeCase(): void
    {
        $nameservers = ResourceFactory::domainNameservers([
            'object' => 'domain_nameservers',
            'domainId' => 'dom_1',
            'domain' => 'send.example.com',
            'nameservers' => ['ns1.example.net'],
            'dnsProvider' => 'cloudflare',
            'event' => 'evt_1',
        ]);

        $this->assertSame('dom_1', $nameservers->domain_id);
        $this->assertSame('cloudflare', $nameservers->dns_provider);
    }
}
