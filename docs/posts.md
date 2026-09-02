# Posts

`$client->customers->posts()` is a resource handle. It does not list posts and does not call the API. Create, compose, and list always take `externalCustomerId` and `boardType`. There is no account-wide post list.

```php
use XBoard\BoardType;

$posts = $client->customers->posts(); // handle only — does not list or fetch posts

$post = $posts
    ->compose('CRM-1001', BoardType::Shared)
    ->setTitle('Kickoff')
    ->create();

$listed = $posts->list('CRM-1001', BoardType::Shared); // required: customer + board
$post   = $posts->get($postId); // one post by id, not a list

$post->compose()
    ->setTitle('New title')
    ->addNote('more')
    ->update();
```

See [Customers](customers.md).
