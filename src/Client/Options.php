<?php

declare(strict_types=1);

namespace XBoard\Client;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use XBoard\Internal\HttpClient;
use XBoard\Version;

/**
 * Client constructor options. Keys match the JavaScript SDK.
 */
final class Options
{
    public readonly string $apiKey;
    public readonly string $accessToken;
    public readonly int $accessTokenExpiresIn;
    public readonly string $baseUrl;
    /** @var array<string, string> */
    public readonly array $servicePaths;
    public readonly int $timeout;
    public readonly int $maxRetries;
    public readonly int $retryDelayMs;
    /** @var array<string, string> */
    public readonly array $defaultHeaders;
    public readonly string $userAgent;
    public readonly bool $autoRefreshToken;
    public readonly int $tokenLeewaySeconds;
    public readonly ?LoggerInterface $logger;
    public readonly ClientInterface $httpClient;
    /** @var callable|null */
    public readonly mixed $onRequest;
    /** @var callable|null */
    public readonly mixed $onResponse;
    /** @var callable|null */
    public readonly mixed $onError;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $apiKey = trim((string) ($options['apiKey'] ?? ''));
        $accessToken = trim((string) ($options['accessToken'] ?? ''));
        if ($apiKey === '' && $accessToken === '') {
            throw new \InvalidArgumentException('apiKey or accessToken is required');
        }

        $baseUrl = trim((string) ($options['baseUrl'] ?? ''));
        if ($baseUrl === '') {
            throw new \InvalidArgumentException('baseUrl is required');
        }

        $this->apiKey = $apiKey;
        $this->accessToken = $accessToken;
        $this->accessTokenExpiresIn = is_numeric($options['accessTokenExpiresIn'] ?? null)
            ? (int) $options['accessTokenExpiresIn']
            : 900;
        $this->baseUrl = $baseUrl;
        $servicePaths = $options['servicePaths'] ?? [];
        $this->servicePaths = is_array($servicePaths) ? $servicePaths : [];
        $this->timeout = is_numeric($options['timeout'] ?? null) ? (int) $options['timeout'] : 30_000;
        $this->maxRetries = is_numeric($options['maxRetries'] ?? null) ? (int) $options['maxRetries'] : 2;
        $this->retryDelayMs = is_numeric($options['retryDelayMs'] ?? null) ? (int) $options['retryDelayMs'] : 250;
        $defaultHeaders = $options['defaultHeaders'] ?? [];
        $this->defaultHeaders = is_array($defaultHeaders) ? $defaultHeaders : [];
        $uaBase = 'xboard-php/' . Version::VERSION;
        $suffix = $options['userAgent'] ?? '';
        $this->userAgent = is_string($suffix) && $suffix !== '' ? $uaBase . ' ' . $suffix : $uaBase;
        $this->autoRefreshToken = is_bool($options['autoRefreshToken'] ?? null)
            ? $options['autoRefreshToken']
            : ($apiKey !== '');
        $this->tokenLeewaySeconds = is_numeric($options['tokenLeewaySeconds'] ?? null)
            ? (int) $options['tokenLeewaySeconds']
            : 30;
        $logger = $options['logger'] ?? null;
        $this->logger = $logger instanceof LoggerInterface ? $logger : null;
        $httpClient = $options['httpClient'] ?? null;
        $this->httpClient = $httpClient instanceof ClientInterface
            ? $httpClient
            : HttpClient::defaultGuzzleClient();
        $this->onRequest = is_callable($options['onRequest'] ?? null) ? $options['onRequest'] : null;
        $this->onResponse = is_callable($options['onResponse'] ?? null) ? $options['onResponse'] : null;
        $this->onError = is_callable($options['onError'] ?? null) ? $options['onError'] : null;
    }
}
