# xboard/php

Official PHP SDK for xBoard partner integrations.

Create and update posts on a customer’s shared or private board. Authenticate with an API key (`xbk_live_…` / `xbk_test_…`). Server-side only. PHP 8.2+. The customer must already exist in xBoard.

## Installation

```bash
composer require xboard/php
```

Published on [Packagist](https://packagist.org/packages/xboard/php).

## Quick start

```php
use XBoard\BoardType;
use XBoard\FileUpload;
use XBoard\XBoard;

$client = new XBoard([
    'apiKey' => getenv('XBOARD_API_KEY'),
    'baseUrl' => getenv('XBOARD_BASE_URL'),
]);

$posts = $client->customers->posts(); // handle only — does not list or fetch posts

$post = $posts
    ->compose('CRM-1001', BoardType::Shared)
    ->setTitle('Kickoff')
    ->addNote('Hello from the partner SDK')
    ->addFile(new FileUpload("hello\n", 'hello.txt', 'text/plain'))
    ->create();
```

`$client->customers->posts()` is a resource handle. It does not list posts and does not call the API. Create, compose, and list always take `externalCustomerId` and `boardType`. There is no account-wide post list.

```php
$listed = $posts->list('CRM-1001', BoardType::Shared);
```

Copy `.env.example` to `.env` for local examples.

```bash
make help
make install
make run-example list-customers
make run-example compose-create
XBOARD_POST_ID=<id> make run-example compose-update
make smoke
```

## Documentation

| Guide | Description |
| ----- | ----------- |
| [Getting started](docs/getting-started.md) | Install, configure, and write a post |
| [Customers](docs/customers.md) | Posts, composer, list, and notes |
| [Authentication](docs/authentication.md) | API keys and token refresh |
| [Configuration](docs/configuration.md) | Client options, timeouts, and hooks |
| [Errors](docs/errors.md) | Error types and handling |
| [Publishing](docs/publishing.md) | Packagist registration and git tags |

A printable partner sheet is in [docs/partner-handoff.html](docs/partner-handoff.html).

## Examples

- [list-customers.php](examples/list-customers.php) — list CRM customers
- [list-posts.php](examples/list-posts.php) — list posts on one customer board
- [list-notes.php](examples/list-notes.php) — list notes and files on a post (`XBOARD_POST_ID`)
- [compose-create.php](examples/compose-create.php) — create a post with notes and a file
- [compose-update.php](examples/compose-update.php) — append to an existing post (`XBOARD_POST_ID`)
- [create-customer-post.php](examples/create-customer-post.php) — titled post without composer parts

## Development

```bash
make install
make validate
```

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Releasing

Bump `XBoard\Version::VERSION` and [CHANGELOG.md](CHANGELOG.md), merge to `main`, then tag a new version. GitHub Actions creates a GitHub Release and updates Packagist. See [docs/publishing.md](docs/publishing.md).

## Security

Report vulnerabilities per [SECURITY.md](SECURITY.md). Never commit API keys.
