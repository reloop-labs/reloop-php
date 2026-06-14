# Reloop PHP SDK

## Before you send

You need two things:

1. **API key** — create one in your Reloop account
2. **Verified domain** — add and verify a sending domain; use it in the `from` address

For setup details and the full API reference, see [reloop.sh/docs](https://reloop.sh/docs).

## Send email

```bash
composer require reloop/reloop-email
```

```php
<?php

use Reloop\Reloop;

$reloop = Reloop::client('rl_your_api_key_here');

$result = $reloop->mail->send([
    'from' => 'Reloop <hello@your-verified-domain.com>',
    'to' => 'user@example.com',
    'subject' => 'Welcome to Reloop',
    'html' => '<p>Thanks for signing up.</p>',
    'text' => 'Thanks for signing up.',
]);

echo $result->message_id, ' ', $result->id;
```

More examples and optional fields: [reloop.sh/docs](https://reloop.sh/docs)
