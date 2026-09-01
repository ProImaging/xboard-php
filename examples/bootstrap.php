<?php

declare(strict_types=1);

use XBoard\BoardType;
use XBoard\XBoard;

require dirname(__DIR__) . '/vendor/autoload.php';

$apiKey = getenv('XBOARD_API_KEY');
$baseUrl = getenv('XBOARD_BASE_URL');
if ($apiKey === false || $apiKey === '' || $baseUrl === false || $baseUrl === '') {
    fwrite(STDERR, "Set XBOARD_API_KEY and XBOARD_BASE_URL\n");
    exit(1);
}

$externalCustomerId = getenv('XBOARD_EXTERNAL_CUSTOMER_ID');
if (!is_string($externalCustomerId) || $externalCustomerId === '') {
    $externalCustomerId = 'CRM-1001';
}

$boardTypeRaw = getenv('XBOARD_BOARD_TYPE');
$boardType = is_string($boardTypeRaw) && $boardTypeRaw !== ''
    ? BoardType::from(strtolower($boardTypeRaw))
    : BoardType::Shared;

return [
    'client' => new XBoard([
        'apiKey' => $apiKey,
        'baseUrl' => $baseUrl,
    ]),
    'externalCustomerId' => $externalCustomerId,
    'boardType' => $boardType,
];
