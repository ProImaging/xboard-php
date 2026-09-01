# Errors

Failed requests throw subclasses of `XBoardError`. Use `instanceof` or inspect `statusCode` / `errorCode`.

## Hierarchy

| Class                 | Typical HTTP status | When                              |
| --------------------- | ------------------- | --------------------------------- |
| `AuthenticationError` | 401                 | Invalid or expired credentials    |
| `PermissionError`     | 403                 | Missing scope or forbidden action |
| `NotFoundError`       | 404                 | Resource not found                |
| `RateLimitError`      | 429                 | Rate limited                      |
| `APIError`            | other 4xx/5xx       | General API failure               |
| `APIConnectionError`  | —                   | Network failure                   |
| `APITimeoutError`     | —                   | Request exceeded timeout          |

## Properties

```php
use XBoard\BoardType;
use XBoard\Errors\RateLimitError;
use XBoard\Errors\XBoardError;

try {
    $client->customers->posts()->list('CRM-1001', BoardType::Shared);
} catch (RateLimitError $err) {
    error_log($err->getMessage() . ' ' . ($err->requestId ?? ''));
    throw $err;
} catch (XBoardError $err) {
    error_log((string) $err->statusCode . ' ' . (string) $err->errorCode);
    throw $err;
}
```

- `getMessage()` — human-readable summary
- `statusCode` — HTTP status when applicable
- `errorCode` — stable string such as `authentication_error` (PHP reserves `Exception::$code` as an integer)
- `requestId` — from `x-correlation-id` or `x-request-id` when present
- `raw` — parsed response body when available

## Validation errors

Constructor validation (empty `apiKey` / `baseUrl`, missing `externalCustomerId`) throws `\InvalidArgumentException`, not `XBoardError`.
