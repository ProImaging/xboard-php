# Authentication

Pass an API key (`xbk_live_…` or `xbk_test_…`) when you construct the client. The SDK obtains a short-lived access token and refreshes it before it expires (`autoRefreshToken` is on by default).

## Token exchange

```php
$exchanged = $client->auth->exchange();
$accessToken = $exchanged['accessToken'];
$expiresIn = $exchanged['expiresIn'];
$tokenType = $exchanged['tokenType'];
```

You do not need to call this yourself when automatic refresh is enabled.

## Automatic refresh (default)

With `autoRefreshToken: true` (default when `apiKey` is set):

- The first request exchanges the API key if no valid token is cached
- Later requests reuse the cached token until near expiry
- `tokenLeewaySeconds` (default `30`) refreshes early

```php
$client = new XBoard([
    'apiKey' => getenv('XBOARD_API_KEY'),
    'baseUrl' => getenv('XBOARD_BASE_URL'),
    'tokenLeewaySeconds' => 60,
]);
```

## Manual token control

```php
use XBoard\BoardType;

$client = new XBoard([
    'apiKey' => getenv('XBOARD_API_KEY'),
    'baseUrl' => getenv('XBOARD_BASE_URL'),
    'autoRefreshToken' => false,
]);

$client->refreshAccessToken();
$client->customers->posts()->list('CRM-1001', BoardType::Shared);

$client->clearAccessToken();
```

If `autoRefreshToken` is `false` and no token is cached, requests throw `AuthenticationError`.

## Seed an existing token

When the caller already has an access token:

```php
$client = new XBoard([
    'accessToken' => $token,
    'accessTokenExpiresIn' => 900,
    'baseUrl' => getenv('XBOARD_BASE_URL'),
    'autoRefreshToken' => false,
]);
```

Without `apiKey`, `refreshAccessToken()` cannot run. Provide `apiKey` as well if you still want automatic refresh.

## Security practices

- Store keys in a secrets manager, not source control
- Do not log `apiKey` or raw token exchange responses in production
- Use test keys (`xbk_test_…`) outside production
