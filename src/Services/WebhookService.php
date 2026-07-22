<?php

namespace Reloop\Services;

use Reloop\Exceptions\ReloopValidationException;
use Reloop\ReloopClient;
use Reloop\Support\Validators;

class WebhookService
{
    private const WEBHOOK_V1 = '/api/webhook/v1';

    /** @var list<string> */
    private const WEBHOOK_STATUSES = ['active', 'paused', 'disabled', 'failed'];

    /** @var list<string> */
    private const DELIVERY_STATUSES = ['pending', 'success', 'failed', 'retrying'];

    public function __construct(private ReloopClient $client)
    {
    }

    /**
     * @return array{id: string, event: string, payload: array<string, mixed>, timestamp: float}
     */
    public static function constructEvent(
        string $payload,
        ?string $signature,
        string $secret,
        int $tolerance = WebhookVerify::DEFAULT_TOLERANCE_SECONDS,
    ): array {
        return WebhookVerify::constructEvent($payload, $signature, $secret, $tolerance);
    }

    /**
     * @param array{
     *   payload: string,
     *   headers?: array<string, string|null>|null,
     *   secret: string,
     *   tolerance?: int|null
     * } $params
     * @return array{id: string, event: string, payload: array<string, mixed>, timestamp: float}
     */
    public function verify(array $params): array
    {
        return WebhookVerify::verify($params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function create(array $params): array
    {
        return $this->client->request('POST', self::WEBHOOK_V1 . '/', $params) ?? [];
    }

    /**
     * @param array{
     *   page?: int,
     *   limit?: int,
     *   status?: string,
     *   organizationId?: string,
     *   userId?: string
     * }|null $params
     * @return array<string, mixed>
     */
    public function list(?array $params = null): array
    {
        $query = self::buildListWebhooksQuery($params);

        return $this->client->request('GET', self::WEBHOOK_V1, null, $query) ?? [];
    }

    /** @return array<string, mixed> */
    public function get(string $webhookId): array
    {
        $id = self::requireWebhookId($webhookId);

        return $this->client->request('GET', self::WEBHOOK_V1 . '/' . $id) ?? [];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function update(string $webhookId, array $params): array
    {
        $id = self::requireWebhookId($webhookId);
        $body = self::validateUpdateParams($params);

        return $this->client->request('PATCH', self::WEBHOOK_V1 . '/' . $id, $body) ?? [];
    }

    /** @return array<string, mixed> */
    public function delete(string $webhookId): array
    {
        $id = self::requireWebhookId($webhookId);

        return $this->client->request('DELETE', self::WEBHOOK_V1 . '/' . $id) ?? [];
    }

    /** @return array<string, mixed> */
    public function pause(string $webhookId): array
    {
        return $this->update($webhookId, ['status' => 'paused']);
    }

    /** @return array<string, mixed> */
    public function enable(string $webhookId): array
    {
        return $this->update($webhookId, ['status' => 'active']);
    }

    /** @return array<string, mixed> */
    public function disable(string $webhookId): array
    {
        return $this->update($webhookId, ['status' => 'disabled']);
    }

    /**
     * @param array{
     *   event?: string,
     *   payload?: array<string, mixed>,
     *   organizationId?: string,
     *   userId?: string
     * } $params
     * @return array<string, mixed>
     */
    public function trigger(array $params): array
    {
        $body = self::validateTriggerParams($params);

        return $this->client->request('POST', self::WEBHOOK_V1 . '/trigger', $body) ?? [];
    }

    /**
     * @param array{page?: int, limit?: int, status?: string}|null $params
     * @return array<string, mixed>
     */
    public function listDeliveries(string $webhookId, ?array $params = null): array
    {
        $id = self::requireWebhookId($webhookId);
        $query = self::buildListDeliveriesQuery($params);

        return $this->client->request('GET', self::WEBHOOK_V1 . '/' . $id . '/deliveries', null, $query) ?? [];
    }

    /** @return array<string, mixed> */
    public function retryDelivery(string $deliveryId): array
    {
        $id = self::requireDeliveryId($deliveryId);

        return $this->client->request('POST', '/api/webhook/deliveries/' . $id . '/retry', []) ?? [];
    }

    private static function requireWebhookId(string $webhookId): string
    {
        try {
            return Validators::requireNonEmptyString($webhookId, 'webhookId');
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                'Webhook webhookId is required and must be a non-empty string.',
                'webhookId',
            );
        }
    }

    private static function requireDeliveryId(string $deliveryId): string
    {
        try {
            return Validators::requireNonEmptyString($deliveryId, 'deliveryId');
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                'Webhook delivery deliveryId is required and must be a non-empty string.',
                'deliveryId',
            );
        }
    }

    private static function requireWebhookStatus(string $status, string $field): void
    {
        if (!in_array($status, self::WEBHOOK_STATUSES, true)) {
            throw new ReloopValidationException('update status must be a valid webhook status.', $field);
        }
    }

    private static function requireDeliveryStatus(string $status, string $field): void
    {
        if (!in_array($status, self::DELIVERY_STATUSES, true)) {
            throw new ReloopValidationException('listDeliveries status must be a valid delivery status.', $field);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function validateUpdateParams(array $params): array
    {
        if ($params === []) {
            throw new ReloopValidationException('update requires at least one field to change.', 'params');
        }

        $body = [];

        if (array_key_exists('description', $params)) {
            if (!is_string($params['description'])) {
                throw new ReloopValidationException(
                    'update description must be a string when provided.',
                    'description',
                );
            }
            $body['description'] = $params['description'];
        }
        if (array_key_exists('name', $params)) {
            if (!is_string($params['name'])) {
                throw new ReloopValidationException('update name must be a string when provided.', 'name');
            }
            $body['name'] = $params['name'];
        }
        if (array_key_exists('url', $params)) {
            if (!is_string($params['url'])) {
                throw new ReloopValidationException('update url must be a string when provided.', 'url');
            }
            $body['url'] = $params['url'];
        }
        if (array_key_exists('secret', $params)) {
            if (!is_string($params['secret'])) {
                throw new ReloopValidationException('update secret must be a string when provided.', 'secret');
            }
            $body['secret'] = $params['secret'];
        }
        if (array_key_exists('status', $params)) {
            if (!is_string($params['status'])) {
                throw new ReloopValidationException('update status must be a valid webhook status.', 'status');
            }
            self::requireWebhookStatus($params['status'], 'status');
            $body['status'] = $params['status'];
        }
        if (array_key_exists('customHeaders', $params)) {
            if ($params['customHeaders'] !== null && !is_array($params['customHeaders'])) {
                throw new ReloopValidationException(
                    'update customHeaders must be an object or null when provided.',
                    'customHeaders',
                );
            }
            $body['customHeaders'] = $params['customHeaders'];
        }
        if (array_key_exists('rateLimitEnabled', $params)) {
            if (!is_bool($params['rateLimitEnabled'])) {
                throw new ReloopValidationException(
                    'update rateLimitEnabled must be a boolean when provided.',
                    'rateLimitEnabled',
                );
            }
            $body['rateLimitEnabled'] = $params['rateLimitEnabled'];
        }
        if (array_key_exists('maxRequestsPerMinute', $params)) {
            if (!is_int($params['maxRequestsPerMinute']) && !is_float($params['maxRequestsPerMinute'])) {
                throw new ReloopValidationException(
                    'update maxRequestsPerMinute must be a number when provided.',
                    'maxRequestsPerMinute',
                );
            }
            $body['maxRequestsPerMinute'] = $params['maxRequestsPerMinute'];
        }
        if (array_key_exists('maxRetries', $params)) {
            if (!is_int($params['maxRetries']) && !is_float($params['maxRetries'])) {
                throw new ReloopValidationException(
                    'update maxRetries must be a number when provided.',
                    'maxRetries',
                );
            }
            $body['maxRetries'] = $params['maxRetries'];
        }
        if (array_key_exists('retryBackoffMultiplier', $params)) {
            if (!is_int($params['retryBackoffMultiplier']) && !is_float($params['retryBackoffMultiplier'])) {
                throw new ReloopValidationException(
                    'update retryBackoffMultiplier must be a number when provided.',
                    'retryBackoffMultiplier',
                );
            }
            $body['retryBackoffMultiplier'] = $params['retryBackoffMultiplier'];
        }
        if (array_key_exists('filteringOptions', $params)) {
            if ($params['filteringOptions'] !== null && !is_array($params['filteringOptions'])) {
                throw new ReloopValidationException(
                    'update filteringOptions must be an object or null when provided.',
                    'filteringOptions',
                );
            }
            $body['filteringOptions'] = $params['filteringOptions'];
        }

        if ($body === []) {
            throw new ReloopValidationException('update requires at least one field to change.', 'params');
        }

        return $body;
    }

    /**
     * @param array{
     *   event?: string,
     *   payload?: array<string, mixed>,
     *   organizationId?: string,
     *   userId?: string
     * } $params
     * @return array<string, mixed>
     */
    private static function validateTriggerParams(array $params): array
    {
        if ($params === []) {
            throw new ReloopValidationException('trigger params are required and must be an object.', 'params');
        }

        $event = Validators::requireNonEmptyString($params['event'] ?? null, 'event');
        if (!isset($params['payload']) || !is_array($params['payload'])) {
            throw new ReloopValidationException('trigger payload is required and must be an object.', 'payload');
        }

        $body = [
            'event' => $event,
            'payload' => $params['payload'],
        ];

        if (isset($params['organizationId'])) {
            $body['organizationId'] = Validators::requireNonEmptyString($params['organizationId'], 'organizationId');
        }
        if (isset($params['userId'])) {
            $body['userId'] = Validators::requireNonEmptyString($params['userId'], 'userId');
        }

        return $body;
    }

    /**
     * @param array{
     *   page?: int,
     *   limit?: int,
     *   status?: string,
     *   organizationId?: string,
     *   userId?: string
     * }|null $params
     * @return array<string, scalar>
     */
    private static function buildListWebhooksQuery(?array $params): array
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
        if (isset($params['status']) && $params['status'] !== '') {
            self::requireWebhookStatus($params['status'], 'status');
            $query['status'] = $params['status'];
        }
        if (isset($params['organizationId'])) {
            $query['organizationId'] = Validators::requireNonEmptyString($params['organizationId'], 'organizationId');
        }
        if (isset($params['userId'])) {
            $query['userId'] = Validators::requireNonEmptyString($params['userId'], 'userId');
        }

        return $query;
    }

    /**
     * @param array{page?: int, limit?: int, status?: string}|null $params
     * @return array<string, scalar>
     */
    private static function buildListDeliveriesQuery(?array $params): array
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
        if (isset($params['status']) && $params['status'] !== '') {
            self::requireDeliveryStatus($params['status'], 'status');
            $query['status'] = $params['status'];
        }

        return $query;
    }
}
