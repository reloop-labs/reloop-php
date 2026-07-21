<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class InboxThreadsService
{
    private const THREADS_V1 = '/api/inbox/v1/threads';
    private const BATCH_IDS_MAX = 100;

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{
     *   mailboxId?: string,
     *   limit?: int,
     *   offset?: int,
     *   folder?: string,
     *   q?: string,
     *   isPinned?: bool,
     *   filter?: string
     * }|null $params
     * @return array<int, array<string, mixed>>
     */
    public function list(?array $params = null): array
    {
        $query = $this->buildListThreadsQuery($params);

        return $this->client->request('GET', self::THREADS_V1, null, $query) ?? [];
    }

    /**
     * @param array{
     *   ids?: list<string>,
     *   action?: string
     * } $params
     * @return array<string, mixed>
     */
    public function batch(array $params = []): array
    {
        $ids = Validators::requireInboxIdArray($params['ids'] ?? null, 'ids', self::BATCH_IDS_MAX);
        Validators::requireThreadBatchAction($params['action'] ?? null);
        $body = [
            'ids' => $ids,
            'action' => $params['action'],
        ];

        return $this->client->request('POST', self::THREADS_V1 . '/batch', $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        $threadId = Validators::requireThreadId($id, 'id');

        return $this->client->request('GET', self::THREADS_V1 . '/' . $threadId) ?? [];
    }

    /** @return array<string, mixed> */
    public function getAttachment(string $id, string $attachmentId): array
    {
        $threadId = Validators::requireThreadId($id, 'id');
        $attachment = Validators::requireInboxAttachmentId($attachmentId, 'attachmentId');

        return $this->client->request(
            'GET',
            self::THREADS_V1 . '/' . $threadId . '/attachments/' . $attachment,
        ) ?? [];
    }

    /**
     * @param array{
     *   isRead?: bool,
     *   isStarred?: bool,
     *   isImportant?: bool,
     *   isPinned?: bool,
     *   status?: string
     * } $params
     * @return array<string, mixed>
     */
    public function update(string $id, array $params = []): array
    {
        $threadId = Validators::requireThreadId($id, 'id');
        if (($params['isRead'] ?? null) === null
            && ($params['isStarred'] ?? null) === null
            && ($params['isImportant'] ?? null) === null
            && ($params['isPinned'] ?? null) === null
            && ($params['status'] ?? null) === null) {
            throw new ReloopValidationException(
                'update requires at least one of isRead, isStarred, isImportant, isPinned, or status.',
                'params',
            );
        }
        if (array_key_exists('status', $params) && $params['status'] !== null) {
            Validators::requireThreadStatus($params['status'], 'status');
        }

        $body = array_filter([
            'isRead' => $params['isRead'] ?? null,
            'isStarred' => $params['isStarred'] ?? null,
            'isImportant' => $params['isImportant'] ?? null,
            'isPinned' => $params['isPinned'] ?? null,
            'status' => $params['status'] ?? null,
        ], static fn ($v) => $v !== null);

        return $this->client->request('PATCH', self::THREADS_V1 . '/' . $threadId, $body) ?? [];
    }

    /**
     * @param array{isRead?: bool} $params
     * @return array<string, mixed>
     */
    public function setRead(string $id, array $params = []): array
    {
        $threadId = Validators::requireThreadId($id, 'id');
        $body = ['isRead' => $params['isRead'] ?? false];

        return $this->client->request('PATCH', self::THREADS_V1 . '/' . $threadId . '/read', $body) ?? [];
    }

    /**
     * @param array{isStarred?: bool} $params
     * @return array<string, mixed>
     */
    public function setStar(string $id, array $params = []): array
    {
        $threadId = Validators::requireThreadId($id, 'id');
        $body = ['isStarred' => $params['isStarred'] ?? false];

        return $this->client->request('PATCH', self::THREADS_V1 . '/' . $threadId . '/star', $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function archive(string $id): array
    {
        $threadId = Validators::requireThreadId($id, 'id');

        return $this->client->request('POST', self::THREADS_V1 . '/' . $threadId . '/archive') ?? [];
    }

    /** @return array<string, mixed> */
    public function trash(string $id): array
    {
        $threadId = Validators::requireThreadId($id, 'id');

        return $this->client->request('POST', self::THREADS_V1 . '/' . $threadId . '/trash') ?? [];
    }

    /** @return array<string, mixed> */
    public function restore(string $id): array
    {
        $threadId = Validators::requireThreadId($id, 'id');

        return $this->client->request('POST', self::THREADS_V1 . '/' . $threadId . '/restore') ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $id): array
    {
        $threadId = Validators::requireThreadId($id, 'id');

        return $this->client->request('DELETE', self::THREADS_V1 . '/' . $threadId) ?? [];
    }

    /**
     * @param array{
     *   mailboxId?: string,
     *   limit?: int,
     *   offset?: int,
     *   folder?: string,
     *   q?: string,
     *   isPinned?: bool,
     *   filter?: string
     * }|null $params
     * @return array<string, scalar|null>
     */
    private function buildListThreadsQuery(?array $params): array
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
        if (isset($params['folder'])) {
            $query['folder'] = $params['folder'];
        }
        if (isset($params['q'])) {
            $query['q'] = $params['q'];
        }
        if (isset($params['isPinned'])) {
            $query['isPinned'] = $params['isPinned'] ? 'true' : 'false';
        }
        if (isset($params['filter'])) {
            Validators::requireThreadFilter($params['filter'], 'filter');
            $query['filter'] = $params['filter'];
        }

        return $query;
    }
}
