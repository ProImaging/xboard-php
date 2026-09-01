<?php

declare(strict_types=1);

namespace XBoard;

use XBoard\Auth\AuthResource;
use XBoard\Client\Options;
use XBoard\Customers\CustomersResource;
use XBoard\Errors\AuthenticationError;
use XBoard\Internal\HttpClient;
use XBoard\Internal\TokenCache;

/**
 * Official xBoard PHP partner SDK.
 *
 * Talks in `externalCustomerId` + `boardType` (`shared`|`private`). The CRM customer must
 * already exist. Default API keys can create/list customer posts, notes, and files.
 *
 * @example
 * ```php
 * use XBoard\BoardType;
 * use XBoard\XBoard;
 *
 * $client = new XBoard([
 *     'apiKey' => getenv('XBOARD_API_KEY'),
 *     'baseUrl' => 'https://api.example.com',
 * ]);
 * $post = $client->customers->posts()->create(
 *     externalCustomerId: 'CRM-1001',
 *     boardType: BoardType::Shared,
 *     title: 'Kickoff',
 * );
 * ```
 */
final class XBoard
{
    public readonly AuthResource $auth;
    public readonly CustomersResource $customers;

    private readonly string $apiKey;
    private readonly HttpClient $http;
    private readonly TokenCache $tokens;
    private readonly bool $autoRefreshToken;
    private readonly int $tokenLeewaySeconds;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $opts = new Options($options);
        $this->apiKey = $opts->apiKey;
        $this->autoRefreshToken = $opts->autoRefreshToken;
        $this->tokenLeewaySeconds = $opts->tokenLeewaySeconds;
        $this->tokens = new TokenCache();
        if ($opts->accessToken !== '') {
            $this->tokens->set(
                $opts->accessToken,
                $opts->accessTokenExpiresIn,
                $this->tokenLeewaySeconds,
            );
        }

        $httpCfg = [
            'baseUrl' => $opts->baseUrl,
            'timeout' => $opts->timeout,
            'maxRetries' => $opts->maxRetries,
            'retryDelayMs' => $opts->retryDelayMs,
            'httpClient' => $opts->httpClient,
            'defaultHeaders' => $opts->defaultHeaders,
            'userAgent' => $opts->userAgent,
        ];
        if ($opts->servicePaths !== []) {
            $httpCfg['servicePaths'] = $opts->servicePaths;
        }
        if ($opts->logger !== null) {
            $httpCfg['logger'] = $opts->logger;
        }
        if ($opts->onRequest !== null) {
            $httpCfg['onRequest'] = $opts->onRequest;
        }
        if ($opts->onResponse !== null) {
            $httpCfg['onResponse'] = $opts->onResponse;
        }
        if ($opts->onError !== null) {
            $httpCfg['onError'] = $opts->onError;
        }

        $this->http = new HttpClient($httpCfg);
        $this->auth = new AuthResource($this->http, $this->apiKey);
        $getToken = fn (): string => $this->getAccessToken();
        $this->customers = new CustomersResource($this->http, $getToken);
    }

    /** Force a new opaque-key exchange and replace the cached access token. */
    public function refreshAccessToken(): string
    {
        if ($this->apiKey === '') {
            throw new AuthenticationError([
                'message' => 'No API key configured to refresh the access token.',
            ]);
        }
        $exchanged = $this->auth->exchange();
        $this->tokens->set($exchanged['accessToken'], $exchanged['expiresIn'], $this->tokenLeewaySeconds);

        return $exchanged['accessToken'];
    }

    /** Clear the in-memory access token (does not revoke the opaque API key). */
    public function clearAccessToken(): void
    {
        $this->tokens->clear();
    }

    private function getAccessToken(): string
    {
        if (!$this->autoRefreshToken) {
            $cached = $this->tokens->get();
            if ($cached !== null) {
                return $cached['accessToken'];
            }
            throw new AuthenticationError([
                'message' => 'No access token cached. Call refreshAccessToken() or enable autoRefreshToken.',
            ]);
        }
        if ($this->tokens->isFresh()) {
            $cached = $this->tokens->get();
            if ($cached === null) {
                throw new AuthenticationError([
                    'message' => 'No access token cached. Call refreshAccessToken() or enable autoRefreshToken.',
                ]);
            }

            return $cached['accessToken'];
        }

        return $this->refreshAccessToken();
    }
}
