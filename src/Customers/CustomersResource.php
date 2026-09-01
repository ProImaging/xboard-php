<?php

declare(strict_types=1);

namespace XBoard\Customers;

use XBoard\BoardType;
use XBoard\Internal\BoardKind;
use XBoard\Internal\HttpClient;

/**
 * @phpstan-type RequestOptions array{timeout?: int, headers?: array<string, string>}
 * @phpstan-type AccessTokenProvider callable(): string
 */
final class CustomersResource
{
    /** @var AccessTokenProvider */
    private $getAccessToken;

    /**
     * @param AccessTokenProvider $getAccessToken
     */
    public function __construct(
        private readonly HttpClient $http,
        callable $getAccessToken,
    ) {
        $this->getAccessToken = $getAccessToken;
    }

    public function posts(): CustomerPosts
    {
        return new CustomerPosts($this->http, $this->getAccessToken);
    }

    /**
     * Optional ensure-only path when you need the typed board without creating a post.
     * Posting does not require this call — `posts()->create()` / `compose()->create()` auto-ensure.
     */
    public function board(string $externalCustomerId, BoardType|string $boardType): CustomerBoard
    {
        $externalCustomerId = BoardKind::requireExternalCustomerId($externalCustomerId);
        $boardType = BoardKind::normalize($boardType);
        $token = ($this->getAccessToken)();
        $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/board',
            'method' => 'POST',
            'authToken' => $token,
            'body' => [
                'boardType' => $boardType,
                'externalID' => $externalCustomerId,
            ],
        ]);

        return new CustomerBoard($this->http, $this->getAccessToken, $externalCustomerId, $boardType);
    }
}
