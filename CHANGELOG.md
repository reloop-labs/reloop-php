# Changelog

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
