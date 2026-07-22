<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\MailService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class MailServiceTest extends TestCase
{
    public function testSendHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [
            'success' => true,
            'messageId' => 'msg_1',
            'status' => 'queued',
            'timestamp' => 't',
            'id' => 'em_1',
        ]);
        $res = $factory->client()->mail->send([
            'from' => 'a@example.com',
            'to' => 'b@example.com',
            'subject' => 'Hi',
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/mail/v1/send', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('a@example.com', $body['from']);
        $this->assertTrue($res['success']);
        $this->assertSame('em_1', $res['id']);
    }

    public function testSendApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(400, ['message' => 'invalid']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->mail->send([
            'from' => 'a@x.com',
            'to' => 'b@x.com',
            'subject' => 'Hi',
        ]);
    }

    public function testSendValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->mail->send([
                'from' => '',
                'to' => 'b@x.com',
                'subject' => 'Hi',
            ]);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('from', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(MailService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === MailService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(['send'], $methods);
    }
}
