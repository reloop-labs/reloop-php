<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\DomainService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class DomainServiceTest extends TestCase
{
    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['id' => 'dom_1', 'domain' => 'example.com', 'object' => 'domain']);
        $res = $factory->client()->domain->create(['domain' => 'example.com']);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/domain/v1/create', $req->getUri()->getPath());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('example.com', $body['domain']);
        $this->assertSame('dom_1', $res['id']);
    }

    public function testValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->domain->get('  ');
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('domainId', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(404, ['message' => 'missing']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->domain->get('dom_missing');
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(DomainService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === DomainService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(['create', 'delete', 'get', 'list', 'update', 'verify'], $methods);
    }
}
