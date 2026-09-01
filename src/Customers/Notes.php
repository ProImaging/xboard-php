<?php

declare(strict_types=1);

namespace XBoard\Customers;

use XBoard\Internal\HttpClient;
use XBoard\Internal\Things;

/**
 * @phpstan-type RequestOptions array{timeout?: int, headers?: array<string, string>}
 * @phpstan-type AccessTokenProvider callable(): string
 */
final class Notes
{
    /** @var AccessTokenProvider */
    private $getAccessToken;

    /**
     * @param AccessTokenProvider $getAccessToken
     */
    public function __construct(
        private readonly HttpClient $http,
        callable $getAccessToken,
        private readonly string $postId,
    ) {
        $this->getAccessToken = $getAccessToken;
    }

    /**
     * @param RequestOptions $options
     *
     * @return array{message: string, status: int, data: array{info: list<array<string, mixed>>, total: int}}
     */
    public function list(array $options = []): array
    {
        $token = ($this->getAccessToken)();
        $raw = $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/post/notes',
            'method' => 'POST',
            'authToken' => $token,
            'body' => ['postId' => $this->postId],
            'options' => $options,
        ]);

        $contents = [];
        foreach (Things::collectThings($raw) as $item) {
            if (Things::isNoteThing($item) || Things::isFileThing($item)) {
                $contents[] = Things::flattenThingContent($item);
            }
        }

        $envelope = is_array($raw) ? $raw : [];

        return [
            'message' => is_string($envelope['message'] ?? null) ? $envelope['message'] : 'ok',
            'status' => is_int($envelope['status'] ?? null) ? $envelope['status'] : 1,
            'data' => [
                'info' => $contents,
                'total' => count($contents),
            ],
        ];
    }
}
