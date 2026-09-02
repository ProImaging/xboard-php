# Customers

Identify a customer with `externalCustomerId` and `boardType` (`BoardType::Shared` or `BoardType::Private`). Customers are created in xBoard, not with this SDK.

List CRM customers with `$client->customers->list()` (`customers:read`). `$client->customers->posts()` is a resource handle. It does not list posts and does not call the API. Create, compose, and list posts always take `externalCustomerId` and `boardType`. There is no account-wide post list.

## List customers

```php
$listed = $client->customers->list(['limit' => 50, 'search' => 'acme']);
$items = $listed['data']['items'];
```

Defaults: `limit` 50, `sortBy` `recentlyUpdated`, `sortOrder` `desc`. Optional `cursor` and `search`.

## Write a post

```php
use XBoard\BoardType;

$posts = $client->customers->posts(); // handle only — does not list or fetch posts

$post = $posts->compose(externalCustomerId: 'CRM-1001', boardType: BoardType::Shared)
    ->setTitle('Kickoff')              // at most once; extra setTitle() throws
    ->addNote('content 1')
    ->addNote('content 2')
    ->addFile('/path/file.txt')
    ->create();
```

A titled post with no notes or files:

```php
$post = $posts->create(
    externalCustomerId: 'CRM-1001',
    boardType: BoardType::Shared,
    title: 'Kickoff',
);
```

Composer `create()` throws if the composer is already bound to an existing post (`$post->compose()`).

## Append on an existing post

```php
$post = $posts->get($postId);

$post->compose()
    ->setTitle('Site visit — complete')
    ->addNote('Customer signed off.')
    ->addFile($upload)
    ->update();
```

You can also call `$post->addNote()`, `$post->addFile()`, and `$post->setTitle()` directly. `update()` throws if there is no post yet. Title replaces; notes and files append.

Composer parts are sent in order. On the first error the sequence stops; the returned `Post` includes whatever already landed.

## List posts and contents

List is scoped to one customer and board. Pass both arguments every time:

```php
$listed = $posts->list('CRM-1001', BoardType::Shared);
$post   = $listed[0];
$notes  = $post->notes()->list();
```

`$listed` is a list of `Post` objects for that customer and `boardType` only. `$notes['data']['info']` includes notes and files.

`$posts->get($postId)` loads one post by id. It is not a list and does not take `externalCustomerId`.

See [`examples/list-customers.php`](../examples/list-customers.php), [`examples/list-posts.php`](../examples/list-posts.php), [`examples/list-notes.php`](../examples/list-notes.php), [`examples/compose-create.php`](../examples/compose-create.php), [`examples/compose-update.php`](../examples/compose-update.php), and [`examples/create-customer-post.php`](../examples/create-customer-post.php).
