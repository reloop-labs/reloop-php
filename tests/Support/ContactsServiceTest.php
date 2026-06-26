<?php

namespace Reloop\Tests\Support;

use PHPUnit\Framework\TestCase;
use Reloop\ReloopClient;
use Reloop\Services\ContactsService;

final class ContactsServiceTest extends TestCase
{
    public function testCreateUsesContactsCreateRoute(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/contacts/create',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['email'] ?? null) === 'user@example.com'
                        && ($options['json']['firstName'] ?? null) === 'Ada';
                }),
            )
            ->willReturn(['id' => 'con_1', 'email' => 'user@example.com']);

        $service = new ContactsService($client);
        $result = $service->create([
            'email' => 'user@example.com',
            'first_name' => 'Ada',
        ]);

        $this->assertSame('con_1', $result->id);
    }

    public function testGetUsesRetrieveRoute(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with('GET', '/api/contacts/retrieve/con_1')
            ->willReturn(['id' => 'con_1']);

        $service = new ContactsService($client);
        $service->get('con_1');
    }

    public function testListWithGroupIdDelegatesToGroupsService(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api/contacts/v1/groups/grp_1/contacts',
                $this->callback(static function (array $options): bool {
                    return ($options['query']['page'] ?? null) === 1;
                }),
            )
            ->willReturn(['object' => 'contact_group', 'contacts' => []]);

        $service = new ContactsService($client);
        $service->list(['groupId' => 'grp_1', 'page' => 1]);
    }

    public function testCreatePropertyUsesPropertiesCreateRoute(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/contacts/v1/properties/create',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['propertyName'] ?? null) === 'company';
                }),
            )
            ->willReturn(['id' => 'prop_1']);

        $service = new ContactsService($client);
        $service->createProperty(['property_name' => 'company', 'property_type' => 'string']);
    }

    public function testChannelsAddContactUsesChannelRoute(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/contacts/channel/ch_1',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['contactId'] ?? null) === 'con_1';
                }),
            )
            ->willReturn(['id' => 'ch_1']);

        $service = new ContactsService($client);
        $result = $service->channels->addContact('ch_1', ['contact_id' => 'con_1']);

        $this->assertSame('ch_1', $result->id);
    }
}
