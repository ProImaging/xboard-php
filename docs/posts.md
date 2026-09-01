# Posts

Use `$client->customers->posts()`.

```php
use XBoard\BoardType;

$posts = $client->customers->posts();

$post = $posts
    ->compose('CRM-1001', BoardType::Shared)
    ->setTitle('Kickoff')
    ->create();

$listed = $posts->list('CRM-1001', BoardType::Shared);
$post   = $posts->get($postId);

$post->compose()
    ->setTitle('New title')
    ->addNote('more')
    ->update();
```

See [Customers](customers.md).
