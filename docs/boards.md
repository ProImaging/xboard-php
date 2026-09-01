# Boards

Posts belong to a customer’s shared or private board. Choose with `BoardType`:

```php
use XBoard\BoardType;

$post = $client->customers->posts()
    ->compose('CRM-1001', BoardType::Shared)
    ->setTitle('Kickoff')
    ->create();
```

To list posts without creating one:

```php
$listed = $client->customers->posts()->list('CRM-1001', BoardType::Shared);
```

See [Customers](customers.md).
