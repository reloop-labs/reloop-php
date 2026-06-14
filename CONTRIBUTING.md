# Contributing to the Reloop PHP SDK

Composer package: **`reloop/reloop-email`**.

**License:** [Apache License 2.0](./LICENSE) with additional use restrictions from Reloop Labs.

**API reference:** [reloop.sh/docs](https://reloop.sh/docs)

Port new endpoints from the [Node.js SDK](https://github.com/reloop-labs/reloop-node) reference.

---

## Development setup

```bash
git clone git@github.com:reloop-labs/reloop-php.git
cd reloop-php
composer install
vendor/bin/phpunit
```

Requires **PHP 8.1+**.

---

## Project layout

```
src/
  Reloop.php
  ReloopClient.php
  Services/           # MailService, DomainService, …
  Support/            # Parameters, ResourceFactory
tests/Support/        # PHPUnit route tests
composer.json         # version
```

---

## Conventions

| Topic | Rule |
|-------|------|
| Mail & domain | `Parameters::forSnakeRequest()` |
| Contacts & API keys | `Parameters::forRequest()` |
| Responses | `Parameters::forResponse()` → snake_case properties |
| Tests | Mock `ReloopClient::request`; assert path and JSON |
| README | Minimal send example + link to docs |

---

## Pull request checklist

- [ ] `vendor/bin/phpunit` passes
- [ ] `composer validate --strict` passes
- [ ] Version in `composer.json` bumped only for releases

---

## Releasing

Version: **`composer.json`** → `"version"`.

```bash
git commit -am "chore: release v1.9.0"
git push origin main
git tag v1.9.0
git push origin v1.9.0
```

[`.github/workflows/release.yml`](./.github/workflows/release.yml) uploads a source zip to GitHub Releases.

Packagist mirrors GitHub tags automatically once the package is registered.
