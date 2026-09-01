<?php

declare(strict_types=1);

namespace XBoard\Customers;

use XBoard\Internal\HttpClient;

/**
 * @phpstan-type AccessTokenProvider callable(): string
 */
final class BoundCustomerPosts
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

    /**
     * @param array{limit?: int} $params
     * @param array{timeout?: int, headers?: array<string, string>} $options
     *
     * @return list<Post>
     */
    public function list(array $params = [], array $options = []): array
    {
        return (new CustomerPosts($this->http, $this->getAccessToken))->list(
            $this->externalCustomerId,
            $this->boardType,
            $params,
            $options,
        );
    }
}
