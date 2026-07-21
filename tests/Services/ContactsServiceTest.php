<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\ContactsService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class ContactsServiceTest extends TestCase
{
    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [
            'object' => 'contact',
            'id' => 'con_1',
            'email' => 'john@example.com',
            'status' => 'subscribed',
            'event' => 'contact.created',
        ]);
        $res = $factory->client()->contacts->create([
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'status' => 'subscribed',
            'properties' => ['company' => 'Reloop'],
            'groupIds' => ['grp_1'],
            'channels' => [['channelId' => 'chn_1', 'subscription' => 'opt_in']],
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/contacts/create', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('john@example.com', $body['email']);
        $this->assertSame('John', $body['firstName']);
        $this->assertSame('con_1', $res['id']);
    }

    public function testCreateApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(409, ['message' => 'exists']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->contacts->create(['email' => 'john@example.com']);
    }

    public function testCreateValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->contacts->create(['email' => 'not-an-email']);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('email', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testGetHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [
            'object' => 'contact',
            'id' => 'con_1',
            'email' => 'a@x.com',
            'status' => 'subscribed',
        ]);
        $res = $factory->client()->contacts->get('con_1');
        $req = $factory->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/api/contacts/retrieve/con_1', $req->getUri()->getPath());
        $this->assertSame('con_1', $res['id']);
    }

    public function testListHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'contact', 'event' => 'contacts.listed']);
        $factory->client()->contacts->list([
            'page' => 2,
            'limit' => 10,
            'search' => 'john',
            'status' => 'subscribed',
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/api/contacts/list', $req->getUri()->getPath());
        parse_str($req->getUri()->getQuery(), $query);
        $this->assertSame('2', $query['page']);
        $this->assertSame('10', $query['limit']);
    }

    public function testUpdateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['id' => 'con_1', 'email' => 'new@x.com', 'event' => 'contact.updated']);
        $factory->client()->contacts->update('con_1', ['email' => 'new@x.com']);
        $req = $factory->lastRequest();
        $this->assertSame('PATCH', $req->getMethod());
        $this->assertSame('/api/contacts/con_1', $req->getUri()->getPath());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('new@x.com', $body['email']);
    }

    public function testUpdateValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->contacts->update('con_1', []);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('params', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testDeleteHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['success' => true, 'object' => 'contact', 'id' => 'con_1']);
        $res = $factory->client()->contacts->delete('con_1');
        $req = $factory->lastRequest();
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/api/contacts/con_1', $req->getUri()->getPath());
        $this->assertTrue($res['success']);
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(ContactsService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === ContactsService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(['create', 'delete', 'get', 'list', 'update'], $methods);
    }
}
