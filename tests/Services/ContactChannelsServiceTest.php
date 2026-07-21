<?php

namespace Reloop\Tests\Services;

use PHPUnit\Framework\TestCase;
use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Services\ContactChannelsService;
use Reloop\Tests\Support\TestClientFactory;
use ReflectionClass;
use ReflectionMethod;

class ContactChannelsServiceTest extends TestCase
{
    public function testCreateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, [
            'object' => 'channel',
            'id' => 'chn_1',
            'name' => 'Newsletter',
            'event' => 'channel.created',
        ]);
        $res = $factory->client()->contacts->channels->create(['name' => 'Newsletter']);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/contacts/v1/channels/create', $req->getUri()->getPath());
        $this->assertSame('rl_test', $req->getHeaderLine('x-api-key'));
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('Newsletter', $body['name']);
        $this->assertSame('chn_1', $res['id']);
    }

    public function testCreateApiError(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(400, ['message' => 'invalid']);
        $this->expectException(ReloopApiException::class);
        $factory->client()->contacts->channels->create(['name' => 'Newsletter']);
    }

    public function testCreateValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->contacts->channels->create(['name' => '']);
            $this->fail('expected validation');
        } catch (ReloopValidationException $e) {
            $this->assertSame('name', $e->field);
            $this->assertSame(0, $factory->hits());
        }
    }

    public function testListHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'channel']);
        $factory->client()->contacts->channels->list();
        $req = $factory->lastRequest();
        $this->assertSame('GET', $req->getMethod());
        $this->assertSame('/api/contacts/v1/channels/list', $req->getUri()->getPath());
    }

    public function testGetHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['object' => 'channel', 'id' => 'chn_1']);
        $factory->client()->contacts->channels->get('chn_1');
        $req = $factory->lastRequest();
        $this->assertSame('/api/contacts/v1/channels/chn_1', $req->getUri()->getPath());
    }

    public function testUpdateHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['id' => 'chn_1']);
        $factory->client()->contacts->channels->update('chn_1', ['name' => 'Updated']);
        $req = $factory->lastRequest();
        $this->assertSame('PATCH', $req->getMethod());
        $this->assertSame('/api/contacts/v1/channels/chn_1', $req->getUri()->getPath());
    }

    public function testDeleteHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['success' => true, 'id' => 'chn_1']);
        $factory->client()->contacts->channels->delete('chn_1');
        $req = $factory->lastRequest();
        $this->assertSame('DELETE', $req->getMethod());
        $this->assertSame('/api/contacts/v1/channels/chn_1', $req->getUri()->getPath());
    }

    public function testAddContactHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['subscriptionId' => 'sub_1']);
        $factory->client()->contacts->channels->addContact('chn_1', [
            'email' => 'a@x.com',
            'subscription' => 'opt_in',
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('/api/contacts/channel/chn_1', $req->getUri()->getPath());
        $body = json_decode((string) $req->getBody(), true);
        $this->assertSame('a@x.com', $body['email']);
    }

    public function testUpdateSubscriptionHappyPath(): void
    {
        $factory = new TestClientFactory();
        $factory->respond(200, ['success' => true, 'status' => 'enrolled']);
        $factory->client()->contacts->channels->updateSubscription('chn_1', [
            'email' => 'a@x.com',
            'subscription' => 'opt_out',
        ]);
        $req = $factory->lastRequest();
        $this->assertSame('PATCH', $req->getMethod());
        $this->assertSame('/api/contacts/channel/chn_1', $req->getUri()->getPath());
    }

    public function testUpdateSubscriptionValidationNoHttp(): void
    {
        $factory = new TestClientFactory();
        try {
            $factory->client()->contacts->channels->updateSubscription('chn_1', [
                'subscription' => 'opt_in',
            ]);
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
                (new ReflectionClass(ContactChannelsService::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                static fn (ReflectionMethod $m) => $m->class === ContactChannelsService::class
                    && $m->getName() !== '__construct',
            ),
        );
        sort($methods);
        $this->assertSame(
            ['addContact', 'create', 'delete', 'get', 'list', 'update', 'updateSubscription'],
            $methods,
        );
    }
}
