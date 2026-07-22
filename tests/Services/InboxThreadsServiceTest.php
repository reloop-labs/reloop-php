<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\InboxThreadsService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class InboxThreadsServiceTest extends TestCase
{
    public function testListHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [[
            'id' => 'thr_1',
            'organizationId' => 'org_1',
            'lastMessageAt' => 't',
            'status' => 'active',
        ]]);
        $res = $factory->client()->inbox->threads->list(['limit' => 50]);
        $req = $factory->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/api/inbox/v1/threads', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        parse_str($req->getUri()->getQuery(), $query);
        $this->assertSame('50', $query['limit']);
        $this->assertCount(1, $res);
        $this->assertSame('thr_1', $res[0]['id']);
    }

    public function testApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(400, ['message' => 'invalid']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->inbox->threads->get('thr_missing');
    }

    public function testValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->inbox->threads->batch([
                'ids' => [],
                'action' => 'archive',
            ]);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('ids', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(InboxThreadsService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === InboxThreadsService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame([
            'archive',
            'batch',
            'delete',
            'get',
            'getAttachment',
            'list',
            'restore',
            'setRead',
            'setStar',
            'trash',
            'update',
        ], $methods);
    }
}
