<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\ContactPropertiesService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class ContactPropertiesServiceTest extends TestCase
{
    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [
            'object' => 'contact_property',
            'id' => 'prop_1',
            'propertyName' => 'company',
            'propertyType' => 'string',
            'event' => 'property.created',
        ]);
        $res = $factory->client()->contacts->properties->create([
            'name' => 'company',
            'type' => 'string',
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/contacts/v1/properties/create', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('company', $body['name']);
        $this->assertSame('string', $body['type']);
        $this->assertSame('prop_1', $res['id']);
    }

    public function testCreateApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(400, ['message' => 'invalid']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->contacts->properties->create(['name' => 'company', 'type' => 'string']);
    }

    public function testCreateValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->contacts->properties->create(['name' => '', 'type' => 'string']);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('name', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testListHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'contact_property', 'event' => 'properties.listed']);
        $factory->client()->contacts->properties->list();
        $req = $factory->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/api/contacts/v1/properties/list', $req->getUri()->getPath());
    }

    public function testUpdateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['id' => 'prop_1']);
        $factory->client()->contacts->properties->update('prop_1', ['fallbackValue' => 'default']);
        $req = $factory->lastRequest();
        $this->assertSame('PATCH', $req->getMethod());
        $this->assertSame('/api/contacts/v1/properties/prop_1', $req->getUri()->getPath());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('default', $body['fallbackValue']);
    }

    public function testDeleteHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['success' => true, 'id' => 'prop_1']);
        $factory->client()->contacts->properties->delete('prop_1');
        $req = $factory->lastRequest();
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/api/contacts/v1/properties/prop_1', $req->getUri()->getPath());
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(ContactPropertiesService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === ContactPropertiesService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(['create', 'delete', 'list', 'update'], $methods);
    }
}
