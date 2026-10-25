<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Exceptions\WebhookSignatureException;
use Reloop\Services\WebhookService;
use Reloop\Services\WebhookVerify;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class WebhookServiceTest extends TestCase
{
    private const WEBHOOK_FIXTURE = <<<'JSON'
{"id":"wh_123456789","name":"Production webhook","url":"https://example.com/webhooks/reloop","secret":"whsec_test","status":"active","customHeaders":null,"rateLimitEnabled":false,"maxRequestsPerMinute":60,"maxRetries":3,"retryBackoffMultiplier":2,"filteringOptions":null,"lastTriggeredAt":null,"successCount":0,"failureCount":0,"consecutiveFailures":0,"events":["domain.created"],"createdAt":"2026-01-01T00:00:00.000Z","updatedAt":"2026-01-01T00:00:00.000Z"}
JSON;

    private const SECRET = 'whsec_test_secret';

    private const PAYLOAD = <<<'JSON'
{"id":"evt_123456789","event":"domain.created","payload":{"domainId":"dom_1"},"timestamp":1735689600}
JSON;

    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, json_decode(self::WEBHOOK_FIXTURE, true));
        $res = $factory->client()->webhook->create([
            'description' => 'Production webhook',
            'url' => 'https://example.com/webhooks/reloop',
            'events' => ['domain.created'],
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/webhook/v1/', $req->getUri()->getPath());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('Production webhook', $body['description']);
        $this->assertSame('wh_123456789', $res['id']);
    }

    public function testValidationNoHttp(): void
    {
        $factory = new TestClientFactory();

        try {
            $factory->client()->webhook->get('');
            $this->fail('expected validation for empty webhookId');
        } catch (ReloopValidationException) {
        }

        try {
            $factory->client()->webhook->update('wh_1', []);
            $this->fail('expected validation for empty update params');
        } catch (ReloopValidationException) {
        }

        try {
            $factory->client()->webhook->trigger([]);
            $this->fail('expected validation for empty trigger params');
        } catch (ReloopValidationException) {
        }

        $this->assertSame(0, $factory->hits());
    }

    public function testApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(500, ['message' => 'boom']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->webhook->get('wh_1');
    }

    public function testSurfaceLock(): void
    {
        $instanceMethods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(WebhookService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === WebhookService::class
                    && !$m->isStatic()
                    && $m->getName() !== '__construct',
            ),
        );
        sort($instanceMethods);
        $this->assertSame(
            [
                'create',
                'delete',
                'disable',
                'enable',
                'get',
                'list',
                'listDeliveries',
                'pause',
                'retryDelivery',
                'trigger',
                'update',
                'verify',
            ],
            $instanceMethods,
        );

        $staticMethods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(WebhookService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === WebhookService::class && $m->isStatic(),
            ),
        );
        sort($staticMethods);
        $this->assertSame(['constructEvent'], $staticMethods);
    }

    public function testVerifyAcceptsValidSignature(): void
    {
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PAYLOAD);

        $event = WebhookVerify::verify([
            'payload' => self::PAYLOAD,
            'headers' => [WebhookVerify::WEBHOOK_SIGNATURE_HEADER => $header],
            'secret' => self::SECRET,
        ]);

        $this->assertSame('evt_123456789', $event['id']);
        $this->assertSame('domain.created', $event['event']);
        $this->assertSame('dom_1', $event['payload']['domainId']);
    }

    public function testConstructEventVerifiesSignature(): void
    {
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PAYLOAD);

        $event = WebhookService::constructEvent(self::PAYLOAD, $header, self::SECRET, 300);
        $this->assertSame('domain.created', $event['event']);
    }

    public function testVerifyRejectsWrongSecret(): void
    {
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PAYLOAD);

        $this->expectException(WebhookSignatureException::class);
        WebhookVerify::verify([
            'payload' => self::PAYLOAD,
            'headers' => [WebhookVerify::WEBHOOK_SIGNATURE_HEADER => $header],
            'secret' => 'wrong_secret',
        ]);
    }

    public function testVerifyRejectsExpiredTimestamp(): void
    {
        $timestamp = (string) (time() - 600);
        $header = $this->signPayload(self::SECRET, $timestamp, self::PAYLOAD);

        try {
            WebhookVerify::verify([
                'payload' => self::PAYLOAD,
                'headers' => [WebhookVerify::WEBHOOK_SIGNATURE_HEADER => $header],
                'secret' => self::SECRET,
                'tolerance' => 300,
            ]);
            $this->fail('expected WebhookSignatureException');
        } catch (WebhookSignatureException $e) {
            $this->assertStringContainsStringIgnoringCase('tolerance', $e->getMessage());
        }
    }

    public function testInstanceVerifyDelegates(): void
    {
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PAYLOAD);

        $factory = new TestClientFactory();
        $event = $factory->client()->webhook->verify([
            'payload' => self::PAYLOAD,
            'headers' => [WebhookVerify::WEBHOOK_SIGNATURE_HEADER => $header],
            'secret' => self::SECRET,
        ]);

        $this->assertSame('evt_123456789', $event['id']);
        $this->assertSame(0, $factory->hits());
    }

    public function testUpdateAllowsNullCustomHeadersAndFilteringOptions(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, self::WEBHOOK_FIXTURE);
        $res = $factory->client()->webhook->update('wh_1', [
            'customHeaders' => null,
            'filteringOptions' => null,
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('PATCH', $req->getMethod());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertArrayHasKey('customHeaders', $body);
        $this->assertNull($body['customHeaders']);
        $this->assertArrayHasKey('filteringOptions', $body);
        $this->assertNull($body['filteringOptions']);
        $this->assertSame('wh_123456789', $res['id']);
    }

    public function testListTreatsEmptyStatusAsUnset(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'list', 'data' => [], 'total' => 0, 'page' => 1, 'limit' => 10]);
        $factory->client()->webhook->list(['status' => '']);
        $req = $factory->lastRequest();
        $this->assertSame('', $req->getUri()->getQuery());
    }

    public function testVerifyHonorsExplicitZeroTolerance(): void
    {
        $header = $this->signPayload(self::SECRET, (string) (time() - 1), self::PAYLOAD);
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessageMatches('/tolerance/i');
        WebhookVerify::verify([
            'payload' => self::PAYLOAD,
            'headers' => [WebhookVerify::WEBHOOK_SIGNATURE_HEADER => $header],
            'secret' => self::SECRET,
            'tolerance' => 0,
        ]);
    }

    private const PRODUCTION_PAYLOAD = '{"id":"whev_01abc","type":"email.received","created_at":"2026-10-26T12:00:00.000Z","data":{"email_id":"in_1","subject":"Hello"}}';

    public function testVerifyAcceptsProductionDelivery(): void
    {
        $this->assertSame('Reloop-Signature', WebhookVerify::WEBHOOK_SIGNATURE_HEADER);
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PRODUCTION_PAYLOAD);

        $event = WebhookVerify::verify([
            'payload' => self::PRODUCTION_PAYLOAD,
            'headers' => [
                'reloop-signature' => $header,
                'reloop-timestamp' => $timestamp,
                'reloop-id' => 'whev_01abc',
            ],
            'secret' => self::SECRET,
        ]);

        $this->assertSame('whev_01abc', $event['id']);
        $this->assertSame('email.received', $event['type']);
        $this->assertSame('2026-10-26T12:00:00.000Z', $event['created_at']);
        $this->assertSame('Hello', $event['data']['subject']);
        $this->assertSame($event['type'], $event['event']);
        $this->assertSame(1793016000.0, $event['timestamp']);
    }

    public function testVerifyAcceptsLegacyHeader(): void
    {
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PRODUCTION_PAYLOAD);

        $event = WebhookVerify::verify([
            'payload' => self::PRODUCTION_PAYLOAD,
            'headers' => ['x-webhook-signature' => $header],
            'secret' => self::SECRET,
        ]);

        $this->assertSame('email.received', $event['type']);
    }

    public function testLegacyEnvelopeGetsCreatedAt(): void
    {
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PAYLOAD);

        $event = WebhookService::constructEvent(self::PAYLOAD, $header, self::SECRET, 300);
        $this->assertSame('2025-01-01T00:00:00.000Z', $event['created_at']);
    }

    public function testVerifyRejectsTamperedProductionBody(): void
    {
        $timestamp = (string) time();
        $header = $this->signPayload(self::SECRET, $timestamp, self::PRODUCTION_PAYLOAD);

        $this->expectException(WebhookSignatureException::class);
        WebhookService::constructEvent(str_replace('Hello', 'Hacked', self::PRODUCTION_PAYLOAD), $header, self::SECRET, 300);
    }

    public function testMissingHeaderNamesReloopSignature(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Reloop-Signature');
        WebhookVerify::verify(['payload' => self::PRODUCTION_PAYLOAD, 'headers' => [], 'secret' => self::SECRET]);
    }

    public function testApiExceptionBodyReadsFixAndLegacyTip(): void
    {
        $withFix = new ReloopApiException(403, 'Forbidden', ['message' => 'Nope', 'why' => 'Scope', 'fix' => 'Use another key']);
        $this->assertSame('Use another key', $withFix->body['fix']);
        $this->assertSame('Use another key', $withFix->body['tip']);

        $withTip = new ReloopApiException(400, 'Bad Request', ['message' => 'x', 'tip' => 'Try again']);
        $this->assertSame('Try again', $withTip->body['fix']);
    }

    private function signPayload(string $secret, string $timestamp, string $payload): string
    {
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return 't=' . $timestamp . ',v1=' . $signature;
    }
}
