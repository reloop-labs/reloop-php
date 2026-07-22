<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\ContactGroupsService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class ContactGroupsServiceTest extends TestCase
{
    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [
            'object' => 'contact_group',
            'id' => 'grp_1',
            'name' => 'VIP',
            'event' => 'group.created',
        ]);
        $res = $factory->client()->contacts->groups->create(['name' => 'VIP']);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/contacts/v1/groups/create', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('VIP', $body['name']);
        $this->assertSame('grp_1', $res['id']);
    }

    public function testCreateApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(400, ['message' => 'invalid']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->contacts->groups->create(['name' => 'VIP']);
    }

    public function testCreateValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->contacts->groups->create(['name' => '']);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('name', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testListHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'contact_group']);
        $factory->client()->contacts->groups->list();
        $req = $factory->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/api/contacts/v1/groups/list', $req->getUri()->getPath());
    }

    public function testGetHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'contact_group', 'id' => 'grp_1']);
        $factory->client()->contacts->groups->get('grp_1');
        $req = $factory->lastRequest();
        $this->assertSame('/api/contacts/v1/groups/grp_1', $req->getUri()->getPath());
    }

    public function testUpdateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['id' => 'grp_1']);
        $factory->client()->contacts->groups->update('grp_1', ['name' => 'New Name']);
        $req = $factory->lastRequest();
        $this->assertSame('PATCH', $req->getMethod());
        $this->assertSame('/api/contacts/v1/groups/grp_1', $req->getUri()->getPath());
    }

    public function testDeleteHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['success' => true, 'id' => 'grp_1']);
        $factory->client()->contacts->groups->delete('grp_1');
        $req = $factory->lastRequest();
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/api/contacts/v1/groups/grp_1', $req->getUri()->getPath());
    }

    public function testListContactsHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'contact_group']);
        $factory->client()->contacts->groups->listContacts('grp_1');
        $req = $factory->lastRequest();
        $this->assertSame('/api/contacts/v1/groups/grp_1/contacts', $req->getUri()->getPath());
    }

    public function testAddContactHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['success' => true, 'id' => 'con_1']);
        $factory->client()->contacts->groups->addContact('grp_1', ['email' => 'a@x.com']);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/contacts/group/grp_1', $req->getUri()->getPath());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('a@x.com', $body['email']);
    }

    public function testRemoveContactHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['success' => true, 'id' => 'con_1']);
        $factory->client()->contacts->groups->removeContact('grp_1', ['contact_id' => 'con_1']);
        $req = $factory->lastRequest();
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/api/contacts/group/grp_1', $req->getUri()->getPath());
    }

    public function testAddContactValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->contacts->groups->addContact('grp_1', []);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('params', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testSurfaceLock(): void
    {
        $methods = array_map(
            static fn (ReflectionMethod $m) => $m->getName(),
            array_filter(
                (new ReflectionClass(ContactGroupsService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === ContactGroupsService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(
            ['addContact', 'create', 'delete', 'get', 'list', 'listContacts', 'removeContact', 'update'],
            $methods,
        );
    }
}
