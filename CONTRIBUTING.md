# Contributing to the Reloop PHP SDK

Composer package: **`reloop/reloop-email`** (version aligned with Node/Python/Go/Java at **2.0.0**).

**License:** [Apache License 2.0](./LICENSE) with additional use restrictions from Reloop Labs.

**API reference:** [reloop.sh/docs](https://reloop.sh/docs)

Port new endpoints from the [Node.js SDK](https://github.com/reloop-labs/reloop-node) — Node wins on paths, bodies, and validation messages.

---

## Development setup

```bash
git clone git@github.com:reloop-labs/reloop-php.git
cd reloop-php
composer install
composer test
# or: vendor/bin/phpunit
```

Requires **PHP 8.1+**. Runtime deps: Guzzle only.

---

## Project layout

```
src/
  Reloop.php / ReloopClient.php / Version.php
  Exceptions/          # ReloopValidationException, ReloopApiException, WebhookSignatureException
  Support/Validators.php
  Services/            # Mail, ApiKey, Domain, Contacts*, Webhook*, Inbox*
tests/
  Support/TestClientFactory.php   # Guzzle MockHandler + history
  *Test.php
composer.json          # version 2.0.0
phpunit.xml.dist
```

---

## Conventions

| Topic | Rule |
|-------|------|
| Errors | `ReloopValidationException` (no HTTP) / `ReloopApiException` (HTTP/network) |
| Wire JSON | Match Node (snake_case vs camelCase per endpoint) |
| Validation | Fail before HTTP; tests assert **0** requests |
| Tests | Guzzle `MockHandler`: method, path, `x-api-key`, body; surface-lock method sets |
| Endpoints | Do not invent — copy from Node `paths.ts` |
| Version | Same semver as Node/Python/Go/Java |

---

## Pull request checklist

- [ ] `composer test` / `vendor/bin/phpunit` passes
- [ ] New ops have happy path + API error + validation (0 HTTP) + surface test
- [ ] `composer.json` version and `Version::VERSION` updated only when releasing

---

## Releasing

1. Set `composer.json` `"version"` and `Reloop\Version::VERSION` to the same value (match other SDKs)
2. Tag and publish via existing GitHub Actions / Packagist
