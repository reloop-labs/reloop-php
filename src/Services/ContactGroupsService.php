<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class ContactGroupsService
{
    private const GROUPS_BASE = '/api/contacts/v1/groups';
    private const GROUP_MEMBERSHIP = '/api/contacts/group';

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @param array{name?: string} $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        $name = self::requireCreateGroupName($params['name'] ?? null, 'name');

        return $this->client->request('POST', self::GROUPS_BASE . '/create', ['name' => $name]) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int, search?: string}|null $params
     * @return array<string, mixed>
     */
    public function list(?array $params = null): array
    {
        $query = self::validateListParams($params);

        return $this->client->request('GET', self::GROUPS_BASE . '/list', null, $query) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        $groupId = self::requireGroupId($id, 'id');

        return $this->client->request('GET', self::GROUPS_BASE . '/' . $groupId) ?? [];
    }

    /**
     * @param array{name?: string} $params
     * @return array<string, mixed>
     */
    public function update(string $id, array $params): array
    {
        $groupId = self::requireGroupId($id, 'id');
        $name = self::requireUpdateGroupName($params['name'] ?? null, 'name');

        return $this->client->request('PATCH', self::GROUPS_BASE . '/' . $groupId, ['name' => $name]) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $id): array
    {
        $groupId = self::requireGroupId($id, 'id');

        return $this->client->request('DELETE', self::GROUPS_BASE . '/' . $groupId) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int, search?: string, status?: string}|null $params
     * @return array<string, mixed>
     */
    public function listContacts(string $id, ?array $params = null): array
    {
        $groupId = self::requireGroupId($id, 'id');
        $query = self::validateListContactsParams($params);

        return $this->client->request(
            'GET',
            self::GROUPS_BASE . '/' . $groupId . '/contacts',
            null,
            $query,
        ) ?? [];
    }

    /**
     * @param array{contact_id?: string, email?: string} $params
     * @return array<string, mixed>
     */
    public function addContact(string $id, array $params): array
    {
        $groupId = self::requireGroupId($id, 'id');
        $body = self::validateMembershipParams($params);

        return $this->client->request('POST', self::GROUP_MEMBERSHIP . '/' . $groupId, $body) ?? [];
    }

    /**
     * @param array{contact_id?: string, email?: string} $params
     * @return array<string, mixed>
     */
    public function removeContact(string $id, array $params): array
    {
        $groupId = self::requireGroupId($id, 'id');
        $body = self::validateMembershipParams($params);

        return $this->client->request('DELETE', self::GROUP_MEMBERSHIP . '/' . $groupId, $body) ?? [];
    }

    private static function requireGroupId(string $id, string $field): string
    {
        try {
            return Validators::requireNonEmptyString($id, $field);
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                "Group {$field} is required and must be a non-empty string.",
                $field,
            );
        }
    }

    private static function requireCreateGroupName(?string $name, string $field): string
    {
        if ($name === null) {
            throw new ReloopValidationException(
                "Group {$field} is required and must be a string.",
                $field,
            );
        }
        $trimmed = trim($name);
        if (mb_strlen($trimmed) < 1) {
            throw new ReloopValidationException(
                "Group {$field} must be at least 1 character.",
                $field,
            );
        }
        if (mb_strlen($trimmed) > 50) {
            throw new ReloopValidationException(
                "Group {$field} must be at most 50 characters.",
                $field,
            );
        }

        return $trimmed;
    }

    private static function requireUpdateGroupName(?string $name, string $field): string
    {
        if ($name === null) {
            throw new ReloopValidationException(
                "Group {$field} is required and must be a string.",
                $field,
            );
        }
        $trimmed = trim($name);
        if (mb_strlen($trimmed) < 1) {
            throw new ReloopValidationException(
                "Group {$field} must be at least 1 character.",
                $field,
            );
        }
        if (mb_strlen($trimmed) > 255) {
            throw new ReloopValidationException(
                "Group {$field} must be at most 255 characters.",
                $field,
            );
        }

        return $trimmed;
    }

    private static function requireListContactStatus(string $status, string $field): void
    {
        if (!in_array($status, ['subscribed', 'unsubscribed', 'blocked'], true)) {
            throw new ReloopValidationException(
                "list {$field} must be \"subscribed\", \"unsubscribed\", or \"blocked\".",
                $field,
            );
        }
    }

    /** @return array<string, string> */
    private static function validateMembershipParams(array $params): array
    {
        $body = [];

        if (array_key_exists('contact_id', $params)) {
            try {
                $body['contact_id'] = Validators::requireNonEmptyString($params['contact_id'], 'contact_id');
            } catch (ReloopValidationException) {
                throw new ReloopValidationException(
                    'contact_id must be a non-empty string when provided.',
                    'contact_id',
                );
            }
        }
        if (array_key_exists('email', $params)) {
            try {
                $body['email'] = Validators::requireNonEmptyString($params['email'], 'email');
            } catch (ReloopValidationException) {
                throw new ReloopValidationException(
                    'email must be a non-empty string when provided.',
                    'email',
                );
            }
        }
        if ($body === []) {
            throw new ReloopValidationException('Either contact_id or email is required.', 'params');
        }

        return $body;
    }

    /** @param array{page?: int, limit?: int, search?: string}|null $params */
    private static function validateListParams(?array $params): array
    {
        $query = [];
        if ($params === null) {
            return $query;
        }
        if (isset($params['page'])) {
            Validators::requirePage((int) $params['page'], 'page');
            $query['page'] = (int) $params['page'];
        }
        if (isset($params['limit'])) {
            Validators::requireLimit((int) $params['limit'], 1, 100, 'limit');
            $query['limit'] = (int) $params['limit'];
        }
        if (isset($params['search'])) {
            $query['search'] = $params['search'];
        }

        return $query;
    }

    /** @param array{page?: int, limit?: int, search?: string, status?: string}|null $params */
    private static function validateListContactsParams(?array $params): array
    {
        $query = [];
        if ($params === null) {
            return $query;
        }
        if (isset($params['page'])) {
            Validators::requirePage((int) $params['page'], 'page');
            $query['page'] = (int) $params['page'];
        }
        if (isset($params['limit'])) {
            Validators::requireLimit((int) $params['limit'], 1, 100, 'limit');
            $query['limit'] = (int) $params['limit'];
        }
        if (isset($params['search'])) {
            $query['search'] = $params['search'];
        }
        if (isset($params['status'])) {
            self::requireListContactStatus((string) $params['status'], 'status');
            $query['status'] = $params['status'];
        }

        return $query;
    }
}
