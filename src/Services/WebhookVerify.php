<?php

namespace Reloop\Services;

use Reloop\Exceptions\WebhookSignatureException;

/** HMAC-SHA256 webhook signature verification. */
final class WebhookVerify
{
    /** Header Reloop signs every delivery with: t=<unix>,v1=<hex>. */
    public const WEBHOOK_SIGNATURE_HEADER = 'Reloop-Signature';
    public const WEBHOOK_TIMESTAMP_HEADER = 'Reloop-Timestamp';
    public const WEBHOOK_ID_HEADER = 'Reloop-Id';

    /** Header name used before 2.0.1. Still accepted as a fallback. */
    private const LEGACY_WEBHOOK_SIGNATURE_HEADER = 'x-webhook-signature';
    public const DEFAULT_TOLERANCE_SECONDS = 300;

    private function __construct()
    {
    }

    /**
     * @param array{
     *   payload: string,
     *   headers?: array<string, string|null>|null,
     *   secret: string,
     *   tolerance?: int|null
     * } $params
     * @return array{id: string, type: string, created_at: string, data: array<string, mixed>, event: string, payload: array<string, mixed>, timestamp: float}
     */
    public static function verify(array $params): array
    {
        $secret = $params['secret'] ?? '';
        if ($secret === '') {
            throw new WebhookSignatureException('Webhook secret is required');
        }

        $payload = $params['payload'] ?? null;
        if ($payload === null || $payload === '') {
            throw new WebhookSignatureException('Webhook payload is required');
        }

        $tolerance = array_key_exists('tolerance', $params)
            ? $params['tolerance']
            : null;
        if ($tolerance === null) {
            $tolerance = self::DEFAULT_TOLERANCE_SECONDS;
        } else {
            $tolerance = (int) $tolerance;
        }

        $signatureHeader = self::getHeader($params['headers'] ?? null, self::WEBHOOK_SIGNATURE_HEADER);
        if ($signatureHeader === null || $signatureHeader === '') {
            $signatureHeader = self::getHeader($params['headers'] ?? null, self::LEGACY_WEBHOOK_SIGNATURE_HEADER);
        }
        if ($signatureHeader === null || $signatureHeader === '') {
            throw new WebhookSignatureException('Missing ' . self::WEBHOOK_SIGNATURE_HEADER . ' header');
        }

        ['timestamp' => $timestamp, 'signatures' => $signatures] = self::parseSignatureHeader($signatureHeader);
        self::verifyTimestamp($timestamp, $tolerance);

        $expected = self::computeExpectedSignature($secret, $timestamp, $payload);
        $valid = false;
        foreach ($signatures as $sig) {
            if (self::verifySignature($expected, $sig)) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            throw new WebhookSignatureException('Webhook signature verification failed');
        }

        return self::parseWebhookEvent($payload);
    }

    /**
     * @return array{id: string, type: string, created_at: string, data: array<string, mixed>, event: string, payload: array<string, mixed>, timestamp: float}
     */
    public static function constructEvent(
        string $payload,
        ?string $signature,
        string $secret,
        int $tolerance = self::DEFAULT_TOLERANCE_SECONDS,
    ): array {
        $headers = [];
        if ($signature !== null && $signature !== '') {
            $headers[self::WEBHOOK_SIGNATURE_HEADER] = $signature;
        }

        return self::verify([
            'payload' => $payload,
            'headers' => $headers,
            'secret' => $secret,
            'tolerance' => $tolerance,
        ]);
    }

    /**
     * @param array<string, string|null>|null $headers
     */
    private static function getHeader(?array $headers, string $name): ?string
    {
        if ($headers === null) {
            return null;
        }

        $lower = strtolower($name);
        foreach ($headers as $key => $value) {
            if (strtolower((string) $key) === $lower) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array{timestamp: string, signatures: list<string>}
     */
    private static function parseSignatureHeader(string $header): array
    {
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $header) as $element) {
            $trimmed = trim($element);
            $eqIndex = strpos($trimmed, '=');
            if ($eqIndex === false) {
                continue;
            }

            $prefix = substr($trimmed, 0, $eqIndex);
            $value = substr($trimmed, $eqIndex + 1);

            if ($prefix === 't') {
                $timestamp = $value;
            } elseif ($prefix === 'v1') {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || $timestamp === '' || $signatures === []) {
            throw new WebhookSignatureException(
                'Invalid ' . self::WEBHOOK_SIGNATURE_HEADER . ' header: expected t= and v1= values',
            );
        }

        return ['timestamp' => $timestamp, 'signatures' => $signatures];
    }

    private static function computeExpectedSignature(string $secret, string $timestamp, string $payload): string
    {
        return hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    }

    private static function verifySignature(string $expected, string $received): bool
    {
        if (!ctype_xdigit($expected) || !ctype_xdigit($received)) {
            return false;
        }

        $expectedBuf = hex2bin($expected);
        $receivedBuf = hex2bin($received);
        if ($expectedBuf === false || $receivedBuf === false) {
            return false;
        }
        if (strlen($expectedBuf) !== strlen($receivedBuf)) {
            return false;
        }

        return hash_equals($expectedBuf, $receivedBuf);
    }

    private static function verifyTimestamp(string $timestamp, int $tolerance): void
    {
        if (!is_numeric($timestamp)) {
            throw new WebhookSignatureException('Invalid timestamp in ' . self::WEBHOOK_SIGNATURE_HEADER . ' header');
        }

        $ts = (float) $timestamp;
        if (!is_finite($ts)) {
            throw new WebhookSignatureException('Invalid timestamp in ' . self::WEBHOOK_SIGNATURE_HEADER . ' header');
        }

        $age = abs(time() - (int) floor($ts));
        if ($age > $tolerance) {
            throw new WebhookSignatureException(
                'Timestamp outside tolerance: allowed drift is ' . $tolerance . ' seconds',
            );
        }
    }

    /**
     * @return array{id: string, type: string, created_at: string, data: array<string, mixed>, event: string, payload: array<string, mixed>, timestamp: float}
     */
    private static function parseWebhookEvent(string $raw): array
    {
        try {
            $record = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new WebhookSignatureException('Webhook payload is not valid JSON');
        }

        if (!is_array($record)) {
            throw new WebhookSignatureException('Webhook payload must be a JSON object');
        }

        $id = $record['id'] ?? null;
        if (!is_string($id) || $id === '') {
            throw new WebhookSignatureException('Webhook payload missing required field: id');
        }

        // Production envelope: {id, type, created_at, data}.
        // Legacy envelope (pre-2.0.1 fixtures): {id, event, payload, timestamp}.
        $type = $record['type'] ?? $record['event'] ?? null;
        if (!is_string($type) || $type === '') {
            throw new WebhookSignatureException('Webhook payload missing required field: type');
        }

        $data = $record['data'] ?? $record['payload'] ?? null;
        if (!is_array($data)) {
            throw new WebhookSignatureException('Webhook payload missing required field: data');
        }

        $createdAt = $record['created_at'] ?? null;
        $legacyTimestamp = $record['timestamp'] ?? null;
        if (is_string($createdAt) && $createdAt !== '') {
            try {
                $at = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                throw new WebhookSignatureException('Webhook payload has an invalid created_at');
            }
            $ts = (float) $at->format('U.v');
        } elseif ((is_int($legacyTimestamp) || is_float($legacyTimestamp)) && is_finite((float) $legacyTimestamp)) {
            $ts = (float) $legacyTimestamp;
            $createdAt = \DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', $ts))
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d\\TH:i:s.v\\Z');
        } else {
            throw new WebhookSignatureException('Webhook payload missing required field: created_at');
        }

        return [
            'id' => $id,
            'type' => $type,
            'created_at' => $createdAt,
            'data' => $data,
            // Deprecated aliases kept for callers of 2.0.0.
            'event' => $type,
            'payload' => $data,
            'timestamp' => $ts,
        ];
    }
}
