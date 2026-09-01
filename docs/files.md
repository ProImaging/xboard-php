# Files

Upload files onto an existing `Post`.

```php
use XBoard\FileUpload;

$post->addFile(new FileUpload(
    file_get_contents('invoice.pdf') ?: '',
    'invoice.pdf',
    'application/pdf',
));

$post->compose()
    ->addFile('/path/file.txt')
    ->update();
```

`$file` may be a filesystem path, `SplFileInfo`, a stream resource, or `XBoard\FileUpload`.

Blocked extensions include `.exe`, `.msi`, `.bat`, and macro-enabled Office formats. See [Customers](customers.md).
