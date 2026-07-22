<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class InboxMessagesService
{
    private const MESSAGES_V1 = '/api/inbox/v1/messages';
    private const BATCH_IDS_MAX = 100;

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{
     *   mailboxId?: string,
     *   limit?: int,
     *   offset?: int,
     *   q?: string,
     *   isSpam?: bool
     * }|null $params
     * @return array<int, array<string, mixed>>
     */
    public function list(?array $params = null): array
    {
        $query = $this->buildListMessagesQuery($params);

        return $this->client->request('GET', self::MESSAGES_V1, null, $query) ?? [];
    }

    /**
     * @param array{mailboxId?: string}|null $params
     * @return array<int, array<string, mixed>>
     */
    public function listSent(?array $params = null): array
    {
        $query = [];
        if ($params !== null && isset($params['mailboxId'])) {
            $query['mailboxId'] = $params['mailboxId'];
        }

        return $this->client->request('GET', self::MESSAGES_V1 . '/sent', null, $query) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        $messageId = Validators::requireMessageId($id, 'id');

        return $this->client->request('GET', self::MESSAGES_V1 . '/' . $messageId) ?? [];
    }

    /**
     * @param array{ids?: list<string>} $params
     * @return array<int, array<string, mixed>>
     */
    public function batch(array $params = []): array
    {
        $ids = Validators::requireInboxIdArray($params['ids'] ?? null, 'ids', self::BATCH_IDS_MAX);
        $body = ['ids' => $ids];

        return $this->client->request('POST', self::MESSAGES_V1 . '/batch', $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function getRaw(string $id): array
    {
        $messageId = Validators::requireMessageId($id, 'id');

        return $this->client->request('GET', self::MESSAGES_V1 . '/' . $messageId . '/raw') ?? [];
    }

    /** @return array<string, mixed> */
    public function getAttachment(string $id, string $attachmentId): array
    {
        $messageId = Validators::requireMessageId($id, 'id');
        $attachment = Validators::requireInboxAttachmentId($attachmentId, 'attachmentId');

        return $this->client->request(
            'GET',
            self::MESSAGES_V1 . '/' . $messageId . '/attachments/' . $attachment,
        ) ?? [];
    }

    /**
     * @param array{
     *   isRead?: bool,
     *   isStarred?: bool,
     *   isSpam?: bool
     * } $params
     * @return array<string, mixed>
     */
    public function update(string $id, array $params = []): array
    {
        $messageId = Validators::requireMessageId($id, 'id');
        if (($params['isRead'] ?? null) === null
            && ($params['isStarred'] ?? null) === null
            && ($params['isSpam'] ?? null) === null) {
            throw new ReloopValidationException(
                'update requires at least one of isRead, isStarred, or isSpam.',
                'params',
            );
        }

        $body = array_filter([
            'isRead' => $params['isRead'] ?? null,
            'isStarred' => $params['isStarred'] ?? null,
            'isSpam' => $params['isSpam'] ?? null,
        ], static fn ($v) => $v !== null);

        return $this->client->request('PATCH', self::MESSAGES_V1 . '/' . $messageId, $body) ?? [];
    }

    /**
     * @param array{isRead?: bool} $params
     * @return array<string, mixed>
     */
    public function setRead(string $id, array $params = []): array
    {
        $messageId = Validators::requireMessageId($id, 'id');
        $body = ['isRead' => $params['isRead'] ?? false];

        return $this->client->request('PATCH', self::MESSAGES_V1 . '/' . $messageId . '/read', $body) ?? [];
    }

    /**
     * @param array{isStarred?: bool} $params
     * @return array<string, mixed>
     */
    public function setStar(string $id, array $params = []): array
    {
        $messageId = Validators::requireMessageId($id, 'id');
        $body = ['isStarred' => $params['isStarred'] ?? false];

        return $this->client->request('PATCH', self::MESSAGES_V1 . '/' . $messageId . '/star', $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $id): array
    {
        $messageId = Validators::requireMessageId($id, 'id');

        return $this->client->request('DELETE', self::MESSAGES_V1 . '/' . $messageId) ?? [];
    }

    /**
     * @param array{
     *   mailboxId?: string,
     *   to?: string|list<string>,
     *   subject?: string,
     *   text?: string,
     *   html?: string,
     *   cc?: string|list<string>,
     *   bcc?: string|list<string>,
     *   attachments?: list<array<string, mixed>>,
     *   scheduledAt?: string,
     *   undoWindowSeconds?: float|int
     * } $params
     * @return array<string, mixed>
     */
    public function send(array $params = []): array
    {
        $mailboxId = Validators::requireNonEmptyString($params['mailboxId'] ?? null, 'mailboxId');
        $to = Validators::requireRecipient($params['to'] ?? null, 'to');
        $subject = Validators::requireNonEmptyString($params['subject'] ?? null, 'subject');
        if (array_key_exists('undoWindowSeconds', $params) && $params['undoWindowSeconds'] !== null) {
            Validators::requireFiniteNumber($params['undoWindowSeconds'], 'undoWindowSeconds');
        }

        $body = array_filter([
            'mailboxId' => $mailboxId,
            'to' => $to,
            'subject' => $subject,
            'text' => $params['text'] ?? null,
            'html' => $params['html'] ?? null,
            'scheduledAt' => $params['scheduledAt'] ?? null,
            'undoWindowSeconds' => $params['undoWindowSeconds'] ?? null,
            'attachments' => $params['attachments'] ?? null,
        ], static fn ($v) => $v !== null);

        if (array_key_exists('cc', $params) && $params['cc'] !== null) {
            $body['cc'] = Validators::requireRecipient($params['cc'], 'cc');
        }
        if (array_key_exists('bcc', $params) && $params['bcc'] !== null) {
            $body['bcc'] = Validators::requireRecipient($params['bcc'], 'bcc');
        }

        return $this->client->request('POST', self::MESSAGES_V1 . '/send', $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function cancelPending(string $id): array
    {
        $messageId = Validators::requireMessageId($id, 'id');

        return $this->client->request(
            'POST',
            self::MESSAGES_V1 . '/pending/' . $messageId . '/cancel',
        ) ?? [];
    }

    /**
     * @param array{
     *   text?: string,
     *   html?: string,
     *   cc?: string|list<string>,
     *   bcc?: string|list<string>,
     *   attachments?: list<array<string, mixed>>
     * } $params
     * @return array<string, mixed>
     */
    public function reply(string $id, array $params = []): array
    {
        $messageId = Validators::requireMessageId($id, 'id');
        $body = $this->buildComposeBody($params);

        return $this->client->request('POST', self::MESSAGES_V1 . '/' . $messageId . '/reply', $body) ?? [];
    }

    /**
     * @param array{
     *   text?: string,
     *   html?: string,
     *   cc?: string|list<string>,
     *   bcc?: string|list<string>,
     *   attachments?: list<array<string, mixed>>
     * } $params
     * @return array<string, mixed>
     */
    public function replyAll(string $id, array $params = []): array
    {
        $messageId = Validators::requireMessageId($id, 'id');
        $body = $this->buildComposeBody($params);

        return $this->client->request('POST', self::MESSAGES_V1 . '/' . $messageId . '/reply-all', $body) ?? [];
    }

    /**
     * @param array{
     *   to?: string|list<string>,
     *   text?: string,
     *   html?: string,
     *   cc?: string|list<string>,
     *   bcc?: string|list<string>,
     *   attachments?: list<array<string, mixed>>
     * } $params
     * @return array<string, mixed>
     */
    public function forward(string $id, array $params = []): array
    {
        $messageId = Validators::requireMessageId($id, 'id');
        $to = Validators::requireRecipient($params['to'] ?? null, 'to');
        $body = $this->buildComposeBody($params);
        $body['to'] = $to;

        return $this->client->request('POST', self::MESSAGES_V1 . '/' . $messageId . '/forward', $body) ?? [];
    }

    /**
     * @param array{
     *   mailboxId?: string,
     *   limit?: int,
     *   offset?: int,
     *   q?: string,
     *   isSpam?: bool
     * }|null $params
     * @return array<string, scalar|null>
     */
    private function buildListMessagesQuery(?array $params): array
    {
        $query = [];
        if ($params === null) {
            return $query;
        }
        if (isset($params['mailboxId'])) {
            $query['mailboxId'] = $params['mailboxId'];
        }
        if (isset($params['limit'])) {
            Validators::requireInboxLimit((int) $params['limit'], 'limit');
            $query['limit'] = (int) $params['limit'];
        }
        if (isset($params['offset'])) {
            Validators::requireInboxOffset((int) $params['offset'], 'offset');
            $query['offset'] = (int) $params['offset'];
        }
        if (isset($params['q'])) {
            $query['q'] = $params['q'];
        }
        if (isset($params['isSpam'])) {
            $query['isSpam'] = $params['isSpam'] ? 'true' : 'false';
        }

        return $query;
    }

    /**
     * @param array{
     *   text?: string,
     *   html?: string,
     *   cc?: string|list<string>,
     *   bcc?: string|list<string>,
     *   attachments?: list<array<string, mixed>>
     * } $params
     * @return array<string, mixed>
     */
    private function buildComposeBody(array $params): array
    {
        Validators::requireComposeBody($params['text'] ?? null, $params['html'] ?? null);

        $body = array_filter([
            'text' => $params['text'] ?? null,
            'html' => $params['html'] ?? null,
            'attachments' => $params['attachments'] ?? null,
        ], static fn ($v) => $v !== null);

        if (array_key_exists('cc', $params) && $params['cc'] !== null) {
            $body['cc'] = Validators::requireRecipient($params['cc'], 'cc');
        }
        if (array_key_exists('bcc', $params) && $params['bcc'] !== null) {
            $body['bcc'] = Validators::requireRecipient($params['bcc'], 'bcc');
        }

        return $body;
    }
}
