<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class InboxMailboxesService
{
    private const MAILBOXES_V1 = '/api/inbox/v1/mailboxes';

    public function __construct(private ReloopClient $client)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function list(): array
    {
        return $this->client->request('GET', self::MAILBOXES_V1 . '/list') ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        $mailboxId = Validators::requireMailboxId($id, 'id');

        return $this->client->request('GET', self::MAILBOXES_V1 . '/' . $mailboxId) ?? [];
    }

    /**
     * @param array{
     *   domainId?: string,
     *   email?: string,
     *   password?: string,
     *   quota?: string,
     *   displayName?: string
     * } $params
     * @return array<string, mixed>
     */
    public function create(array $params = []): array
    {
        $domainId = Validators::requireNonEmptyString($params['domainId'] ?? null, 'domainId');
        $email = Validators::requireNonEmptyString($params['email'] ?? null, 'email');

        $body = array_filter([
            'domainId' => $domainId,
            'email' => $email,
            'password' => $params['password'] ?? null,
            'quota' => $params['quota'] ?? null,
            'displayName' => $params['displayName'] ?? null,
        ], static fn ($v) => $v !== null);

        return $this->client->request('POST', self::MAILBOXES_V1 . '/create', $body) ?? [];
    }

    /**
     * @param array{
     *   displayName?: string,
     *   status?: string,
     *   quota?: string
     * } $params
     * @return array<string, mixed>
     */
    public function update(string $id, array $params = []): array
    {
        $mailboxId = Validators::requireMailboxId($id, 'id');
        if (($params['displayName'] ?? null) === null
            && ($params['status'] ?? null) === null
            && ($params['quota'] ?? null) === null) {
            throw new ReloopValidationException(
                'update requires at least one of displayName, status, or quota.',
                'params',
            );
        }
        if (array_key_exists('status', $params) && $params['status'] !== null) {
            Validators::requireMailboxStatus($params['status'], 'status');
        }

        $body = array_filter([
            'displayName' => $params['displayName'] ?? null,
            'status' => $params['status'] ?? null,
            'quota' => $params['quota'] ?? null,
        ], static fn ($v) => $v !== null);

        return $this->client->request('PATCH', self::MAILBOXES_V1 . '/' . $mailboxId, $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $id): array
    {
        $mailboxId = Validators::requireMailboxId($id, 'id');

        return $this->client->request('DELETE', self::MAILBOXES_V1 . '/' . $mailboxId) ?? [];
    }
}
