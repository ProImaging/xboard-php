<?php

declare(strict_types=1);

/** php examples/list-customers.php */

/** @var array{client: \XBoard\XBoard, externalCustomerId: string, boardType: \XBoard\BoardType} $ctx */
$ctx = require __DIR__ . '/bootstrap.php';

$params = [];
$limit = getenv('XBOARD_LIST_LIMIT');
if (is_string($limit) && $limit !== '') {
    $params['limit'] = (int) $limit;
}

$listed = $ctx['client']->customers->list($params);
echo json_encode($listed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
