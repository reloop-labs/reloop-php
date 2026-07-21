<?php

namespace Reloop\Support;

use Reloop\Exceptions\ReloopValidationException;

final class Validators
{
    public static function requireNonEmptyString(?string $value, string $field): string
    {
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            throw new ReloopValidationException(
                "{$field} is required and must be a non-empty string.",
                $field,
            );
        }

        return $trimmed;
    }

    public static function requireMailString(?string $value, string $field): string
    {
        return self::requireNonEmptyString($value, $field);
    }

    /**
     * @param string|array<int, string>|null $value
     * @return string|list<string>
     */
    public static function requireRecipient(string|array|null $value, string $field): string|array
    {
        if (is_string($value)) {
            return self::requireMailString($value, $field);
        }
        if (is_array($value)) {
            if ($value === []) {
                throw new ReloopValidationException(
                    "{$field} must contain at least one address.",
                    $field,
                );
            }
            $out = [];
            foreach ($value as $entry) {
                if (!is_string($entry)) {
                    throw new ReloopValidationException(
                        "{$field} is required and must be a string or string array.",
                        $field,
                    );
                }
                $out[] = self::requireMailString($entry, $field);
            }

            return $out;
        }

        throw new ReloopValidationException(
            "{$field} is required and must be a string or string array.",
            $field,
        );
    }

    public static function requireApiKeyName(?string $name, string $field): string
    {
        $trimmed = self::requireNonEmptyString($name, $field);
        if (mb_strlen($trimmed) > 255) {
            throw new ReloopValidationException(
                "API key {$field} must be at most 255 characters.",
                $field,
            );
        }

        return $trimmed;
    }

    public static function requireApiKeyId(?string $id, string $field): string
    {
        return self::requireNonEmptyString($id, $field);
    }

    public static function requirePage(int $page, string $field): void
    {
        if ($page < 1) {
            throw new ReloopValidationException(
                "list {$field} must be an integer >= 1.",
                $field,
            );
        }
    }

    public static function requireLimit(int $limit, int $min, int $max, string $field): void
    {
        if ($limit < $min || $limit > $max) {
            throw new ReloopValidationException(
                "list {$field} must be an integer between {$min} and {$max}.",
                $field,
            );
        }
    }

    public static function requireMailboxId(?string $id, string $field): string
    {
        try {
            return self::requireNonEmptyString($id, $field);
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                "Mailbox {$field} is required and must be a non-empty string.",
                $field,
            );
        }
    }

    public static function requireMessageId(?string $id, string $field): string
    {
        try {
            return self::requireNonEmptyString($id, $field);
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                "Message {$field} is required and must be a non-empty string.",
                $field,
            );
        }
    }

    public static function requireThreadId(?string $id, string $field): string
    {
        try {
            return self::requireNonEmptyString($id, $field);
        } catch (ReloopValidationException) {
            throw new ReloopValidationException(
                "Thread {$field} is required and must be a non-empty string.",
                $field,
            );
        }
    }

    public static function requireInboxAttachmentId(?string $id, string $field): string
    {
        return self::requireNonEmptyString($id, $field);
    }

    public static function requireInboxLimit(int $limit, string $field): void
    {
        self::requireLimit($limit, 1, 200, $field);
    }

    public static function requireInboxOffset(int $offset, string $field): void
    {
        if ($offset < 0) {
            throw new ReloopValidationException(
                "list {$field} must be an integer >= 0.",
                $field,
            );
        }
    }

    /**
     * @param list<string>|null $ids
     * @return list<string>
     */
    public static function requireInboxIdArray(?array $ids, string $field, int $max): array
    {
        if ($ids === null || $ids === []) {
            throw new ReloopValidationException(
                "{$field} is required and must be a non-empty array.",
                $field,
            );
        }
        if (count($ids) > $max) {
            throw new ReloopValidationException(
                "{$field} must contain at most {$max} items.",
                $field,
            );
        }
        $out = [];
        foreach ($ids as $i => $id) {
            try {
                $out[] = self::requireNonEmptyString(is_string($id) ? $id : null, $field);
            } catch (ReloopValidationException) {
                throw new ReloopValidationException(
                    "{$field}[{$i}] must be a non-empty string.",
                    $field,
                );
            }
        }

        return $out;
    }

    public static function requireMailboxStatus(?string $status, string $field): void
    {
        if ($status !== 'active' && $status !== 'disabled') {
            throw new ReloopValidationException(
                "{$field} must be \"active\" or \"disabled\".",
                $field,
            );
        }
    }

    public static function requireThreadStatus(?string $status, string $field): void
    {
        if (!in_array($status, ['active', 'archived', 'closed', 'trash'], true)) {
            throw new ReloopValidationException(
                "{$field} must be \"active\", \"archived\", \"closed\", or \"trash\".",
                $field,
            );
        }
    }

    public static function requireThreadFilter(?string $filter, string $field): void
    {
        if (!in_array($filter, ['primary', 'alerts', 'person', 'tag'], true)) {
            throw new ReloopValidationException(
                "{$field} must be \"primary\", \"alerts\", \"person\", or \"tag\".",
                $field,
            );
        }
    }

    public static function requireThreadBatchAction(?string $action): void
    {
        $valid = [
            'archive', 'trash', 'restore', 'star', 'unstar', 'read', 'unread',
            'important', 'unimportant', 'spam', 'unspam', 'pin', 'unpin',
        ];
        if (!in_array($action, $valid, true)) {
            throw new ReloopValidationException(
                'batch action must be a valid thread batch action.',
                'action',
            );
        }
    }

    public static function requireComposeBody(?string $text, ?string $html): void
    {
        if ($text === null && $html === null) {
            throw new ReloopValidationException(
                'at least one of text or html is required.',
                'params',
            );
        }
    }

    public static function requireFiniteNumber(float|int $value, string $field): void
    {
        if (!is_finite((float) $value)) {
            throw new ReloopValidationException(
                "{$field} must be a number when provided.",
                $field,
            );
        }
    }
}
