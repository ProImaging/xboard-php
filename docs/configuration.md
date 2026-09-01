# Configuration

All options are passed to the `XBoard` constructor as an associative array.

## Required

| Option    | Description |
| --------- | ----------- |
| `apiKey`  | API key (`xbk_test_…` or `xbk_live_…`) |
| `baseUrl` | Origin only. Dev: `https://api.dev.sidekiq.com`. Prod: `https://api.xboard.com`. |

`apiKey` is required unless `accessToken` is provided.

## Network

| Option         | Default         | Description                                           |
| -------------- | --------------- | ----------------------------------------------------- |
| `timeout`      | `30000`         | Per-request timeout (ms)                              |
| `maxRetries`   | `2`             | Extra attempts after the first for retryable failures |
| `retryDelayMs` | `250`           | Base delay between retries (multiplied by attempt)    |
| `httpClient`   | Guzzle `Client` | Custom Guzzle client (testing, proxies)               |

Retryable HTTP statuses: `408`, `429`, and `5xx`. Connection errors and timeouts may also retry.

## Headers and User-Agent

```php
$client = new XBoard([
    'apiKey' => '...',
    'baseUrl' => 'https://api.dev.sidekiq.com',
    'defaultHeaders' => ['X-Integration-Id' => 'my-app'],
    'userAgent' => 'my-app/1.0',
]);
```

Default User-Agent: `xboard-php/{VERSION}` plus your suffix.

## Logging and hooks

```php
$client = new XBoard([
    'apiKey' => '...',
    'baseUrl' => 'https://api.dev.sidekiq.com',
    'logger' => $psr3Logger,
    'onRequest' => static function (array $info): void {
        error_log($info['method'] . ' ' . $info['url']);
    },
    'onResponse' => static function (array $info): void {
        error_log((string) $info['status'] . ' ' . (string) $info['durationMs']);
    },
    'onError' => static function (array $info): void {
        error_log((string) $info['error']);
    },
]);
```

`logger` must implement `Psr\Log\LoggerInterface`. Hooks are optional callables.

## Per-request overrides

Resource methods accept optional request options:

```php
use XBoard\BoardType;

$client->customers->posts()->list('CRM-1001', BoardType::Shared, ['limit' => 5], ['timeout' => 10_000]);
```

Supported keys: `timeout` (ms) and `headers`.
