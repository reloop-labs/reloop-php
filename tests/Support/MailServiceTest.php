<?php

namespace Reloop\Tests\Support;

use PHPUnit\Framework\TestCase;
use Reloop\ReloopClient;
use Reloop\Services\MailService;

final class MailServiceTest extends TestCase
{
    public function testSendUsesMailSendRouteWithSnakeCaseBody(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/mail/v1/send',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['from'] ?? null) === 'Reloop <hello@send.example.com>'
                        && ($options['json']['to'] ?? null) === 'user@example.com'
                        && ($options['json']['subject'] ?? null) === 'Welcome to Reloop'
                        && ($options['json']['reply_to'] ?? null) === 'support@example.com'
                        && ($options['json']['tags'] ?? null) === [
                            ['name' => 'campaign', 'value' => 'welcome'],
                        ];
                }),
            )
            ->willReturn([
                'success' => true,
                'messageId' => 'msg_123456789',
                'status' => 'sent',
                'timestamp' => '2026-01-01T00:00:00.000Z',
                'id' => 'log_123456789',
            ]);

        $service = new MailService($client);
        $result = $service->send([
            'from' => 'Reloop <hello@send.example.com>',
            'to' => 'user@example.com',
            'subject' => 'Welcome to Reloop',
            'html' => '<p>Thanks for signing up.</p>',
            'text' => 'Thanks for signing up.',
            'reply_to' => 'support@example.com',
            'tags' => [['name' => 'campaign', 'value' => 'welcome']],
        ]);

        $this->assertTrue($result->success);
        $this->assertSame('msg_123456789', $result->message_id);
        $this->assertSame('log_123456789', $result->id);
    }

    public function testSendSupportsTemplateVariables(): void
    {
        $client = $this->createMock(ReloopClient::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/mail/v1/send',
                $this->callback(static function (array $options): bool {
                    return ($options['json']['to'] ?? null) === ['user@example.com', 'admin@example.com']
                        && ($options['json']['template']['id'] ?? null) === 'tpl_123456789'
                        && ($options['json']['template']['variables']['first_name'] ?? null) === 'Ada';
                }),
            )
            ->willReturn([
                'success' => true,
                'messageId' => 'msg_1',
                'status' => 'sent',
                'timestamp' => '2026-01-01T00:00:00.000Z',
                'id' => 'log_1',
            ]);

        $service = new MailService($client);
        $service->send([
            'from' => 'hello@send.example.com',
            'to' => ['user@example.com', 'admin@example.com'],
            'subject' => 'Your weekly digest',
            'template' => [
                'id' => 'tpl_123456789',
                'variables' => ['first_name' => 'Ada'],
            ],
        ]);
    }
}
