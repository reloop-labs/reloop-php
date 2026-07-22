<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\InboxMailboxesService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class InboxMailboxesServiceTest extends TestCase
{
    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [
            'id' => 'mbx_1',
            'email' => 'user@example.com',
            'status' => 'active',
        ]);
        $res = $factory->client()->inbox->mailboxes->create([
            'domainId' => 'dom_1',
            'email' => 'user@example.com',
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/inbox/v1/mailboxes/create', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('dom_1', $body['domainId']);
        $this->assertSame('user@example.com', $body['email']);
        $this->assertSame('mbx_1', $res['id']);
    }

    public function testApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(400, ['message' => 'invalid']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->inbox->mailboxes->get('mbx_missing');
    }

    public function testValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->inbox->mailboxes->get('  ');
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('id', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(InboxMailboxesService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === InboxMailboxesService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(['create', 'delete', 'get', 'list', 'update'], $methods);
    }
}
