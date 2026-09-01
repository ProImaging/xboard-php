<?php

declare(strict_types=1);

/** php examples/create-customer-post.php */

use XBoard\BoardType;

/** @var array{client: \XBoard\XBoard, externalCustomerId: string, boardType: BoardType} $ctx */
$ctx = require __DIR__ . '/bootstrap.php';

$post = $ctx['client']->customers->posts()->create(
    externalCustomerId: $ctx['externalCustomerId'],
    boardType: BoardType::Shared,
    title: 'Kickoff',
);

echo $post->id . PHP_EOL;
