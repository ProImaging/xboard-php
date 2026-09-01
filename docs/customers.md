# Customers

Identify a customer with `externalCustomerId` and `boardType` (`BoardType::Shared` or `BoardType::Private`). Customers are created in xBoard, not with this SDK.

## Write a post

```php
use XBoard\BoardType;

$posts = $client->customers->posts();

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

```php
$listed = $posts->list('CRM-1001', BoardType::Shared);
$post   = $listed[0];
$notes  = $post->notes()->list();
```

`$listed` is a list of `Post` objects. `$notes['data']['info']` includes notes and files.

See [`examples/compose-create.php`](../examples/compose-create.php), [`examples/compose-update.php`](../examples/compose-update.php), and [`examples/create-customer-post.php`](../examples/create-customer-post.php).
