<?php

declare(strict_types=1);

/** php examples/list-posts.php */

use XBoard\BoardType;

/** @var array{client: \XBoard\XBoard, externalCustomerId: string, boardType: BoardType} $ctx */
$ctx = require __DIR__ . '/bootstrap.php';

$posts = $ctx['client']->customers->posts()->list(
    $ctx['externalCustomerId'],
    $ctx['boardType'],
);

foreach ($posts as $post) {
    echo $post->id . PHP_EOL;
}
