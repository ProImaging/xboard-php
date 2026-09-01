<?php

declare(strict_types=1);

namespace XBoard\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use XBoard\XBoard;

final class MockClient
{
    /**
     * @param list<Response> $responses
     * @param \ArrayObject<int, array> $history
     * @param array<string, mixed> $extra
     */
    public static function xboard(array $responses, \ArrayObject $history, array $extra = []): XBoard
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));
        $http = new Client([
            'handler' => $stack,
            'http_errors' => false,
        ]);

        $options = array_merge([
            'apiKey' => 'xbk_test_secret',
            'baseUrl' => 'https://api.example.com',
            'httpClient' => $http,
            'maxRetries' => 0,
        ], $extra);

        return new XBoard($options);
    }

    /**
     * @param \ArrayObject<int, array> $history
     */
    public static function requestAt(\ArrayObject $history, int $index): RequestInterface
    {
        $entry = $history[$index] ?? null;
        if (!is_array($entry) || !isset($entry['request']) || !$entry['request'] instanceof RequestInterface) {
            throw new \RuntimeException('Missing captured request at index ' . $index);
        }

        return $entry['request'];
    }

    public static function json(int $status, mixed $body): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_THROW_ON_ERROR));
    }

    public static function exchangeOk(): Response
    {
        return self::json(200, [
            'access_token' => 'jwt-access',
            'token_type' => 'Bearer',
            'expires_in' => 900,
        ]);
    }
}
