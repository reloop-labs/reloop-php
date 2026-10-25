# Changelog

## 2.0.1

- Fix: `WebhookVerify` reads the `Reloop-Signature` header Reloop actually sends (`x-webhook-signature` still works as a fallback). `WEBHOOK_SIGNATURE_HEADER` is now `Reloop-Signature`; new `WEBHOOK_TIMESTAMP_HEADER` and `WEBHOOK_ID_HEADER` are `Reloop-Timestamp` and `Reloop-Id`.
- Fix: verified events follow the production envelope (`id`, `type`, `created_at`, `data`). `event`, `payload` and `timestamp` are still returned but deprecated.
- Fix: `ReloopApiException::$body` documents and normalises `fix` (the field the API returns). `tip` is kept as a deprecated mirror of `fix`.

## 2.0.0

Breaking rewrite for Node SDK parity (version aligned with Node/Python/Go/Java `2.0.0`):

- `ReloopValidationException` for invalid client input (no HTTP); `ReloopApiException` for HTTP/network
- Facade: `$apiKey` (was `$apiKeys`), `$mail`, `$domain`, `$contacts`, `$webhook`, `$inbox`
- Typed Contacts with nested `$properties`, `$groups`, `$channels`
- Full Webhook CRUD + pause/enable/disable/trigger/deliveries + local HMAC `WebhookVerify`
- Full Inbox: `$mailboxes`, `$messages`, `$threads`
- Removed: API key `pause`, domain `getNameservers` / `forwardDns`
- Paths aligned with Node (`/api/mail/v1/send`, `/api/api-key/v1/`, `/api/domain/v1/`, …)
- PHPUnit + Guzzle MockHandler tests with surface locks

## 1.8.0

Previous Guzzle client for mail, domain, API keys, and contacts (map/Resource based).
