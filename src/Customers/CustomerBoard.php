<?php

declare(strict_types=1);

namespace XBoard\Customers;

use XBoard\Internal\HttpClient;

/**
 * @phpstan-type AccessTokenProvider callable(): string
 */
final class CustomerBoard
{
    /** @var AccessTokenProvider */
    private $getAccessToken;

    /**
     * @param AccessTokenProvider $getAccessToken
     */
    public function __construct(
        private readonly HttpClient $http,
        callable $getAccessToken,
        private readonly string $externalCustomerId,
        private readonly string $boardType,
    ) {
        $this->getAccessToken = $getAccessToken;
    }

    public function posts(): BoundCustomerPosts
    {
        return new BoundCustomerPosts($this->http, $this->getAccessToken, $this->externalCustomerId, $this->boardType);
    }
}
