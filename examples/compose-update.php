<?php

declare(strict_types=1);

/** php examples/compose-update.php  (needs XBOARD_POST_ID) */

use XBoard\FileUpload;

$postId = getenv('XBOARD_POST_ID');
if (!is_string($postId) || $postId === '') {
    fwrite(STDERR, "Set XBOARD_POST_ID\n");
    exit(1);
}

/** @var array{client: \XBoard\XBoard, externalCustomerId: string, boardType: \XBoard\BoardType} $ctx */
$ctx = require __DIR__ . '/bootstrap.php';

$post = $ctx['client']->customers->posts()->get($postId);
$post->compose()
    ->addNote('appended note')
    ->addFile(new FileUpload("update\n", 'update.txt', 'text/plain'))
    ->update();

echo $post->id . PHP_EOL;
