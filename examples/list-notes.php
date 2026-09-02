<?php

declare(strict_types=1);

/** php examples/list-notes.php  (needs XBOARD_POST_ID) */

$postId = getenv('XBOARD_POST_ID');
if (!is_string($postId) || $postId === '') {
    fwrite(STDERR, "Set XBOARD_POST_ID\n");
    exit(1);
}

/** @var array{client: \XBoard\XBoard, externalCustomerId: string, boardType: \XBoard\BoardType} $ctx */
$ctx = require __DIR__ . '/bootstrap.php';

$notes = $ctx['client']->customers->posts()->get($postId)->notes()->list();
echo json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
