# Notes

Append notes on an existing `Post`.

```php
$post->addNote('hello');
$post->notes()->list();

$post->compose()
    ->addNote('appended note')
    ->update();
```

`$post->notes()->list()` returns notes and files on that post (`data.info`). It is not a customer post list — that is `$posts->list('CRM-1001', BoardType::Shared)`. See [Customers](customers.md).
