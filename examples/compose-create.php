<?php

declare(strict_types=1);

/** php examples/compose-create.php */

use XBoard\BoardType;
use XBoard\FileUpload;

/** @var array{client: \XBoard\XBoard, externalCustomerId: string, boardType: BoardType} $ctx */
$ctx = require __DIR__ . '/bootstrap.php';

$post = $ctx['client']->customers->posts()
    ->compose($ctx['externalCustomerId'], BoardType::Shared)
    ->setTitle('Kickoff')
    ->addNote('Hello from the partner SDK')
    ->addFile(new FileUpload("hello\n", 'hello.txt', 'text/plain'))
    ->create();

echo $post->id . PHP_EOL;
