<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\ApiKeyService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class ApiKeyServiceTest extends TestCase
{
    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['id' => 'key_1', 'key' => 'rl_secret', 'object' => 'api_key']);
        $res = $factory->client()->apiKey->create(['name' => 'prod']);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/api-key/v1/', $req->getUri()->getPath());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('prod', $body['name']);
        $this->assertSame('rl_secret', $res['key']);
    }

    public function testValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        $this->expectException(ReloopValidationException::class);
        try {
            $factory->client()->apiKey->create(['name' => '']);
        } finally {
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(500, ['message' => 'boom']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->apiKey->get('key_1');
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(ApiKeyService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === ApiKeyService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(
            ['create', 'delete', 'disable', 'enable', 'get', 'list', 'rotate', 'update'],
            $methods,
        );
    }
}
