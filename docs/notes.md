# Notes

Append notes on an existing `Post`.

```php
$post->addNote('hello');
$post->notes()->list();

$post->compose()
    ->addNote('appended note')
    ->update();
```

`$post->notes()->list()` returns notes and files on that post (`data.info`). See [Customers](customers.md).
