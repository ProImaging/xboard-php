# Boards

Posts belong to a customer’s shared or private board. Choose with `BoardType`. `$client->customers->posts()` is a handle; it does not list posts. List still needs `externalCustomerId` and `boardType`:

```php
use XBoard\BoardType;

$post = $client->customers->posts()
    ->compose('CRM-1001', BoardType::Shared)
    ->setTitle('Kickoff')
    ->create();
```

To list that customer’s posts on that board (without creating one):

```php
$listed = $client->customers->posts()->list('CRM-1001', BoardType::Shared);
```

There is no account-wide post list.

See [Customers](customers.md).
