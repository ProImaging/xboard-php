<?php

declare(strict_types=1);

namespace XBoard\Customers;

use XBoard\FileUpload;
use XBoard\Internal\HttpClient;
use XBoard\Internal\Multipart;

/**
 * @phpstan-type RequestOptions array{timeout?: int, headers?: array<string, string>}
 * @phpstan-type AccessTokenProvider callable(): string
 */
final class Post
{
    /** @var AccessTokenProvider */
    private $getAccessToken;

    /**
     * @param AccessTokenProvider $getAccessToken
     */
    public function __construct(
        private readonly HttpClient $http,
        callable $getAccessToken,
        public readonly string $id,
    ) {
        $this->getAccessToken = $getAccessToken;
    }

    /**
     * @param RequestOptions $options
     *
     * @return array<string, mixed>
     */
    public function addNote(string $body, array $options = []): array
    {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('body is required');
        }
        $token = ($this->getAccessToken)();
        $result = $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/post/note',
            'method' => 'POST',
            'authToken' => $token,
            'body' => [
                'postId' => $this->id,
                'body' => $body,
            ],
            'options' => $options,
        ]);

        return is_array($result) ? $result : [];
    }

    /**
     * @param string|\SplFileInfo|resource|FileUpload $file
     * @param RequestOptions $options
     *
     * @return array<string, mixed>
     */
    public function addFile(mixed $file, array $options = []): array
    {
        $token = ($this->getAccessToken)();
        $result = $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/post/file?postId=' . rawurlencode($this->id),
            'method' => 'POST',
            'authToken' => $token,
            'multipart' => [Multipart::filePart($file)],
            'options' => $options,
        ]);

        return is_array($result) ? $result : [];
    }

    public function notes(): Notes
    {
        return new Notes($this->http, $this->getAccessToken, $this->id);
    }

    /**
     * Replaces the cover title and isTitle note. Existing notes and files stay.
     *
     * @param RequestOptions $options
     *
     * @return array<string, mixed>
     */
    public function setTitle(string $title, array $options = []): array
    {
        $title = trim($title);
        if ($title === '') {
            throw new \InvalidArgumentException('title is required');
        }
        $token = ($this->getAccessToken)();
        $result = $this->http->request([
            'service' => 'people',
            'path' => '/account/customer/post/title',
            'method' => 'PUT',
            'authToken' => $token,
            'body' => [
                'postId' => $this->id,
                'title' => $title,
            ],
            'options' => $options,
        ]);

        return is_array($result) ? $result : [];
    }

    public function compose(): Composer
    {
        return Composer::forPost($this->http, $this->getAccessToken, $this);
    }
}
