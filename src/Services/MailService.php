<?php

namespace Reloop\Services;

use Reloop\ReloopClient;
use Reloop\Support\Validators;

class MailService
{
    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{
     *   from?: string,
     *   to?: string|list<string>,
     *   subject?: string,
     *   cc?: string|list<string>,
     *   bcc?: string|list<string>,
     *   text?: string,
     *   html?: string,
     *   reply_to?: string|list<string>,
     *   scheduled_at?: string,
     *   headers?: array<string, string>,
     *   channel_id?: string,
     *   attachments?: list<array<string, mixed>>,
     *   tags?: list<array{name: string, value: string}>,
     *   template?: array{id: string, variables?: array<string, mixed>},
     *   thread_id?: string
     * } $params
     * @return array<string, mixed>
     */
    public function send(array $params): array
    {
        $from = Validators::requireMailString($params['from'] ?? null, 'from');
        $to = Validators::requireRecipient($params['to'] ?? null, 'to');
        $subject = Validators::requireMailString($params['subject'] ?? null, 'subject');

        $body = array_filter([
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'cc' => $params['cc'] ?? null,
            'bcc' => $params['bcc'] ?? null,
            'text' => $params['text'] ?? null,
            'html' => $params['html'] ?? null,
            'reply_to' => $params['reply_to'] ?? null,
            'scheduled_at' => $params['scheduled_at'] ?? null,
            'headers' => $params['headers'] ?? null,
            'channel_id' => $params['channel_id'] ?? null,
            'attachments' => $params['attachments'] ?? null,
            'tags' => $params['tags'] ?? null,
            'template' => $params['template'] ?? null,
            'thread_id' => $params['thread_id'] ?? null,
        ], static fn ($v) => $v !== null);

        return $this->client->request('POST', '/api/mail/v1/send', $body) ?? [];
    }
}
