<?php

declare(strict_types=1);

namespace XBoard\Customers;

use XBoard\BoardType;
use XBoard\Internal\BoardKind;
use XBoard\Internal\Envelope;
use XBoard\Internal\HttpClient;

/**
 * @phpstan-type RequestOptions array{timeout?: int, headers?: array<string, string>}
 * @phpstan-type AccessTokenProvider callable(): string
 */
final class CustomerPosts
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

    /**
     * Create a post on the customer's shared/private board (auto-ensures the board).
     * The CRM customer must already exist.
     *
     * @param RequestOptions $options
     */
    public function create(
        string $externalCustomerId,
        BoardType|string $boardType,
        ?string $title = null,
        array $options = [],
    ): Post {
        $externalCustomerId = BoardKind::requireExternalCustomerId($externalCustomerId);
        $boardType = BoardKind::normalize($boardType);
        $body = [
            'boardType' => $boardType,
            'externalID' => $externalCustomerId,
        ];
        $trimmedTitle = trim((string) $title);
        if ($trimmedTitle !== '') {
            $body['title'] = $trimmedTitle;
        }

        $token = ($this->getAccessToken)();
        $result = $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/post',
            'method' => 'POST',
            'authToken' => $token,
            'body' => $body,
            'options' => $options,
        ]);

        return new Post($this->http, $this->getAccessToken, Envelope::postId($result));
    }

    public function compose(string $externalCustomerId, BoardType|string $boardType): Composer
    {
        return Composer::forCreate($this->http, $this->getAccessToken, $externalCustomerId, $boardType);
    }

    /**
     * @param array{limit?: int} $params
     * @param RequestOptions $options
     *
     * @return list<Post>
     */
    public function list(
        string $externalCustomerId,
        BoardType|string $boardType,
        array $params = [],
        array $options = [],
    ): array {
        $externalCustomerId = BoardKind::requireExternalCustomerId($externalCustomerId);
        $boardType = BoardKind::normalize($boardType);
        $body = [
            'boardType' => $boardType,
            'externalID' => $externalCustomerId,
        ];
        if (isset($params['limit'])) {
            $body['limit'] = $params['limit'];
        }

        $token = ($this->getAccessToken)();
        $result = $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/posts',
            'method' => 'POST',
            'authToken' => $token,
            'body' => $body,
            'options' => $options,
        ]);

        $posts = [];
        foreach (Envelope::postIdsFromList($result) as $postId) {
            $posts[] = new Post($this->http, $this->getAccessToken, $postId);
        }

        return $posts;
    }

    /**
     * @param RequestOptions $options
     */
    public function get(string $postId, array $options = []): Post
    {
        $postId = trim($postId);
        if ($postId === '') {
            throw new \InvalidArgumentException('postId is required');
        }
        $token = ($this->getAccessToken)();
        $result = $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/post/get',
            'method' => 'POST',
            'authToken' => $token,
            'body' => ['postId' => $postId],
            'options' => $options,
        ]);

        return new Post($this->http, $this->getAccessToken, Envelope::postId($result));
    }
}
