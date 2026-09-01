<?php

declare(strict_types=1);

namespace XBoard\Customers;

use XBoard\BoardType;
use XBoard\FileUpload;
use XBoard\Internal\BoardKind;
use XBoard\Internal\HttpClient;

/**
 * Ordered composer. `create()` always makes a new post (auto-ensure board).
 * `update()` appends notes/files on an existing post and optionally replaces the title.
 * Parts are sent as separate HTTP calls; the first error stops the sequence.
 *
 * @phpstan-type AccessTokenProvider callable(): string
 * @phpstan-type ComposerPart array{type: 'note', body: string}|array{type: 'file', file: mixed}
 */
final class Composer
{
    /** @var AccessTokenProvider */
    private $getAccessToken;

    private ?string $title = null;

    private bool $hasTitle = false;

    /** @var list<ComposerPart> */
    private array $parts = [];

    /**
     * @param AccessTokenProvider $getAccessToken
     */
    private function __construct(
        private readonly HttpClient $http,
        callable $getAccessToken,
        private readonly ?string $externalCustomerId,
        private readonly BoardType|string|null $boardType,
        private readonly ?Post $post,
    ) {
        $this->getAccessToken = $getAccessToken;
    }

    /**
     * @param AccessTokenProvider $getAccessToken
     */
    public static function forCreate(
        HttpClient $http,
        callable $getAccessToken,
        string $externalCustomerId,
        BoardType|string $boardType,
    ): self {
        return new self($http, $getAccessToken, $externalCustomerId, $boardType, null);
    }

    /**
     * @param AccessTokenProvider $getAccessToken
     */
    public static function forPost(HttpClient $http, callable $getAccessToken, Post $post): self
    {
        return new self($http, $getAccessToken, null, null, $post);
    }

    public function setTitle(string $title): self
    {
        if ($this->hasTitle) {
            throw new \LogicException('setTitle() can be called at most once');
        }
        $trimmed = trim($title);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('title is required');
        }
        $this->hasTitle = true;
        $this->title = $trimmed;

        return $this;
    }

    public function addNote(string $body): self
    {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('note body is required');
        }
        $this->parts[] = ['type' => 'note', 'body' => $body];

        return $this;
    }

    /**
     * @param string|\SplFileInfo|resource|FileUpload $file
     */
    public function addFile(mixed $file): self
    {
        if ($file === null || $file === '') {
            throw new \InvalidArgumentException('file is required');
        }
        $this->parts[] = ['type' => 'file', 'file' => $file];

        return $this;
    }

    public function create(): Post
    {
        if ($this->post !== null) {
            throw new \LogicException('create() cannot run on a composer bound to an existing post; use update()');
        }
        if ($this->externalCustomerId === null || $this->boardType === null) {
            throw new \LogicException('create() requires externalCustomerId and boardType');
        }
        $posts = new CustomerPosts($this->http, $this->getAccessToken);
        $post = $posts->create(
            BoardKind::requireExternalCustomerId($this->externalCustomerId),
            BoardKind::normalize($this->boardType),
            $this->title,
        );
        $this->applyParts($post);

        return $post;
    }

    public function update(): Post
    {
        if ($this->post === null) {
            throw new \LogicException('update() requires an existing post; use create() to make a new post');
        }
        if ($this->title !== null) {
            $this->post->setTitle($this->title);
        }
        $this->applyParts($this->post);

        return $this->post;
    }

    private function applyParts(Post $post): void
    {
        foreach ($this->parts as $part) {
            if ($part['type'] === 'note') {
                $post->addNote($part['body']);
                continue;
            }
            $post->addFile($part['file']);
        }
    }
}
