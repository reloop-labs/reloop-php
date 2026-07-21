# Reloop PHP SDK

Official PHP client for the [Reloop](https://reloop.sh) API.

## Before you send

1. **API key** — create one in your Reloop account
2. **Verified domain** — add and verify a sending domain; use it in the `from` address

Full API reference: [reloop.sh/docs](https://reloop.sh/docs)

## Install

```bash
composer require reloop/reloop-email
```

## Quick start

```php
<?php

require 'vendor/autoload.php';

use Reloop\Exceptions\ReloopApiException;
use Reloop\Exceptions\ReloopValidationException;
use Reloop\Reloop;

$reloop = Reloop::client('rl_your_api_key_here');

try {
    $result = $reloop->mail->send([
        'from' => 'Reloop <hello@your-verified-domain.com>',
        'to' => 'user@example.com',
        'subject' => 'Welcome to Reloop',
        'html' => '<p>Thanks for signing up.</p>',
        'text' => 'Thanks for signing up.',
    ]);
    echo $result['messageId'] . ' ' . $result['id'] . PHP_EOL;
} catch (ReloopValidationException $e) {
    fwrite(STDERR, 'invalid request (' . $e->field . '): ' . $e->getMessage() . PHP_EOL);
} catch (ReloopApiException $e) {
    fwrite(STDERR, 'API error ' . $e->status . ': ' . $e->getMessage() . PHP_EOL);
}
```

## Services

```php
$reloop->apiKey;    // create, list, get, update, delete, rotate, enable, disable
$reloop->mail;      // send
$reloop->domain;    // create, list, get, update, delete, verify
$reloop->contacts;  // CRUD + ->properties, ->groups, ->channels
$reloop->webhook;   // CRUD, pause/enable/disable, trigger, deliveries + verify
$reloop->inbox;     // ->mailboxes, ->messages, ->threads
```

## Errors

| Kind | Type | When |
|------|------|------|
| Bad client args | `ReloopValidationException` | Invalid params — **no HTTP call** |
| HTTP / network | `ReloopApiException` | Non-2xx response or transport failure |
| Webhook HMAC | `WebhookSignatureException` | Local signature verification failed |

Idiomatic PHP exceptions — aligned with the Java SDK and the Node/Python Result / Go `(T, error)` contracts.

## Webhook verification

```php
use Reloop\Services\WebhookVerify;

$event = WebhookVerify::constructEvent($payload, $signature, $secret, 300);
// or $reloop->webhook->verify([...])
```

## License

Licensed under the [Apache License 2.0](./LICENSE) with additional use restrictions from Reloop Labs (same as the [Reloop project](https://github.com/reloop-labs/reloop/blob/main/LICENSE)).
