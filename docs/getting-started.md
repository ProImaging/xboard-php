# Getting started

Install `xboard/php` and create a customer post.

## Prerequisites

- PHP **8.2+** and Composer
- Server-side only (not the browser)
- An API key: `xbk_test_…` or `xbk_live_…`
- Base URL (origin only):
  - Dev: `https://api.dev.sidekiq.com`
  - Prod: `https://api.xboard.com`
- The customer already exists in xBoard. Identify them with `externalCustomerId`.

## Install

```bash
composer require xboard/php
```

## Environment variables

For local scripts, use `.env` (see `.env.example`):

```bash
XBOARD_API_KEY=xbk_test_...
XBOARD_BASE_URL=https://api.dev.sidekiq.com
```

Load them with your preferred loader or `export` in the shell. The SDK does not read `.env` files itself.

## Create a client

```php
use XBoard\XBoard;

$client = new XBoard([
    'apiKey' => getenv('XBOARD_API_KEY'),
    'baseUrl' => getenv('XBOARD_BASE_URL'),
]);
```

The SDK authenticates with the API key and refreshes credentials as needed. `$client->customers->posts()` is a handle, not a list — see [Read posts](#read-posts).

## Write a post

```php
use XBoard\BoardType;
use XBoard\FileUpload;

$posts = $client->customers->posts(); // handle only — does not list or fetch posts

$post = $posts
    ->compose('CRM-1001', BoardType::Shared)
    ->setTitle('Kickoff')
    ->addNote('content 1')
    ->addFile(new FileUpload("hello\n", 'hello.txt', 'text/plain'))
    ->create();
```

`$boardType` is `BoardType::Shared` or `BoardType::Private`. `setTitle()` at most once per composer. `create()` always makes a new post, then sends notes and files in call order. The first error stops the sequence; whatever already landed stays.

A titled post with no notes or files:

```php
$post = $posts->create(
    externalCustomerId: 'CRM-1001',
    boardType: BoardType::Shared,
    title: 'Kickoff',
);
```

## Append on an existing post

```php
$post->compose()
    ->setTitle('Site visit — complete')
    ->addNote('Customer signed off.')
    ->addFile($upload)
    ->update();
```

`update()` requires an existing `Post`. Title replaces; notes and files append. `create()` on `$post->compose()` throws.

If you only have a post id:

```php
$post = $posts->get($postId);
$post->compose()
    ->addNote('Follow-up.')
    ->update();
```

## Read posts

List is scoped to one customer and board. Pass both arguments every time:

```php
$listed = $posts->list('CRM-1001', BoardType::Shared);
$post   = $listed[0];
$notes  = $post->notes()->list();
```

`$listed` is a list of `Post` objects for that customer and `boardType` only. `$notes['data']['info']` contains notes and files on that post. `$posts->get($postId)` loads one post by id; it is not a list.

Runnable copies live in [`examples/`](../examples). `compose-update.php` and `list-notes.php` need `XBOARD_POST_ID`. `make smoke` runs every use case and threads the compose-create id into those scripts.

## Next steps

- [Customers](customers.md) — composer, list, and notes
- [Authentication](authentication.md) — API keys and token refresh
- [Configuration](configuration.md) — timeouts, retries, custom HTTP client
- [Errors](errors.md) — typed errors
