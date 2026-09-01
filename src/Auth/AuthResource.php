<?php

declare(strict_types=1);

namespace XBoard\Auth;

use XBoard\Internal\HttpClient;

final class AuthResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $apiKey,
    ) {
    }

    /**
     * Exchange the opaque API key for a short-lived access JWT.
     *
     * @return array{accessToken: string, tokenType: string, expiresIn: int}
     */
    public function exchange(): array
    {
        /** @var array{access_token?: string, token_type?: string, expires_in?: int} $raw */
        $raw = $this->http->request([
            'service' => 'auth',
            'path' => '/api-keys/exchange',
            'method' => 'POST',
            'authToken' => $this->apiKey,
        ]);

        if (!is_array($raw)) {
            throw new \RuntimeException('Unexpected exchange response');
        }

        return [
            'accessToken' => (string) ($raw['access_token'] ?? ''),
            'tokenType' => (string) ($raw['token_type'] ?? 'Bearer') ?: 'Bearer',
            'expiresIn' => (int) ($raw['expires_in'] ?? 0),
        ];
    }
}
