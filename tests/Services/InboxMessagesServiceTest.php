<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\InboxMessagesService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class InboxMessagesServiceTest extends TestCase
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
        $res = $factory->client()->inbox->messages->send([
            'mailboxId' => 'mbx_1',
            'to' => 'user@example.com',
            'subject' => 'Hello',
            'html' => '<p>Hi</p>',
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/inbox/v1/messages/send', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('mbx_1', $body['mailboxId']);
        $this->assertSame('user@example.com', $body['to']);
        $this->assertSame('Hello', $body['subject']);
        $this->assertTrue($res['success']);
        $this->assertSame('em_1', $res['id']);
    }

    public function testApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(400, ['message' => 'invalid']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->inbox->messages->get('msg_missing');
    }

    public function testValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->inbox->messages->send([
                'mailboxId' => '',
                'to' => 'user@example.com',
                'subject' => 'Hello',
            ]);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('mailboxId', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(InboxMessagesService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === InboxMessagesService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame([
            'batch',
            'cancelPending',
            'delete',
            'forward',
            'get',
            'getAttachment',
            'getRaw',
            'list',
            'listSent',
            'reply',
            'replyAll',
            'send',
            'setRead',
            'setStar',
            'update',
        ], $methods);
    }
}
