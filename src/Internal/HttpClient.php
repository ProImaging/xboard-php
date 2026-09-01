<?php

declare(strict_types=1);

namespace XBoard\Internal;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use XBoard\Errors\APIConnectionError;
use XBoard\Errors\APITimeoutError;
use XBoard\Errors\XBoardError;

final class HttpClient
{
    /**
     * @param array<string, mixed> $cfg
     */
    public function __construct(private readonly array $cfg)
    {
    }

    public static function defaultGuzzleClient(): Client
    {
        return new Client([
            'http_errors' => false,
        ]);
    }

    /**
     * @param array<string, mixed> $req
     */
    public function request(array $req): mixed
    {
        $method = is_string($req['method'] ?? null) ? $req['method'] : 'GET';
        $service = is_string($req['service'] ?? null) ? $req['service'] : '';
        $path = is_string($req['path'] ?? null) ? $req['path'] : '';
        $baseUrl = is_string($this->cfg['baseUrl'] ?? null) ? $this->cfg['baseUrl'] : '';
        $servicePaths = $this->cfg['servicePaths'] ?? null;
        $servicePaths = is_array($servicePaths) ? $servicePaths : null;
        /** @var array<string, string>|null $servicePaths */
        $url = Paths::buildUrl($baseUrl, $service, $path, $servicePaths);

        $defaultHeaders = $this->cfg['defaultHeaders'] ?? [];
        $defaultHeaders = is_array($defaultHeaders) ? $defaultHeaders : [];
        $reqHeaders = $req['headers'] ?? [];
        $reqHeaders = is_array($reqHeaders) ? $reqHeaders : [];
        $optionHeaders = is_array($req['options'] ?? null) ? ($req['options']['headers'] ?? []) : [];
        $optionHeaders = is_array($optionHeaders) ? $optionHeaders : [];
        $userAgent = is_string($this->cfg['userAgent'] ?? null) ? $this->cfg['userAgent'] : '';

        $headers = array_merge(
            [
                'Accept' => 'application/json',
                'User-Agent' => $userAgent,
            ],
            $defaultHeaders,
            $reqHeaders,
            $optionHeaders,
        );

        $authToken = $req['authToken'] ?? null;
        if (is_string($authToken) && $authToken !== '') {
            $headers['Authorization'] = 'Bearer ' . $authToken;
        }

        $isMultipart = isset($req['multipart']);
        $timeoutMs = $req['options']['timeout'] ?? $this->cfg['timeout'];
        $timeoutMs = is_numeric($timeoutMs) ? (int) $timeoutMs : 30_000;
        $maxRetries = is_numeric($this->cfg['maxRetries'] ?? null) ? (int) $this->cfg['maxRetries'] : 2;
        $retryDelayMs = is_numeric($this->cfg['retryDelayMs'] ?? null) ? (int) $this->cfg['retryDelayMs'] : 250;
        $maxAttempts = $isMultipart ? 1 : $maxRetries + 1;
        $lastError = null;
        $http = $this->cfg['httpClient'];
        if (!$http instanceof ClientInterface) {
            throw new \RuntimeException('httpClient is required');
        }

        for ($attempt = 0; $attempt < $maxAttempts; ++$attempt) {
            $started = (int) floor(microtime(true) * 1000);

            try {
                $onRequest = $this->cfg['onRequest'] ?? null;
                $this->invokeHook(is_callable($onRequest) ? $onRequest : null, [
                    'method' => $method,
                    'url' => $url,
                    'headers' => $headers,
                ]);

                $guzzleOptions = [
                    'headers' => $headers,
                    'timeout' => $timeoutMs / 1000,
                    'http_errors' => false,
                ];

                if ($isMultipart) {
                    $guzzleOptions['multipart'] = $req['multipart'];
                    unset($guzzleOptions['headers']['Content-Type']);
                } elseif (array_key_exists('body', $req) && $req['body'] !== null) {
                    $guzzleOptions['headers']['Content-Type'] = 'application/json';
                    $guzzleOptions['body'] = json_encode(
                        $req['body'],
                        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
                    );
                }

                $res = $http->request($method, $url, $guzzleOptions);
                $durationMs = (int) floor(microtime(true) * 1000) - $started;

                $onResponse = $this->cfg['onResponse'] ?? null;
                $this->invokeHook(is_callable($onResponse) ? $onResponse : null, [
                    'method' => $method,
                    'url' => $url,
                    'status' => $res->getStatusCode(),
                    'durationMs' => $durationMs,
                ]);

                $text = (string) $res->getBody();
                $parsed = null;
                if ($text !== '') {
                    try {
                        $parsed = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\JsonException) {
                        $parsed = $text;
                    }
                }

                if ($res->getStatusCode() < 200 || $res->getStatusCode() >= 300) {
                    $requestId = $res->getHeaderLine('x-correlation-id');
                    if ($requestId === '') {
                        $requestId = $res->getHeaderLine('x-request-id');
                    }
                    $err = XBoardError::fromStatus(
                        $res->getStatusCode(),
                        self::messageFromBody($parsed, $res->getReasonPhrase() !== '' ? $res->getReasonPhrase() : 'HTTP ' . $res->getStatusCode()),
                        $parsed,
                        $requestId !== '' ? $requestId : null,
                    );
                    if ($this->shouldRetry($res->getStatusCode()) && $attempt < $maxAttempts - 1) {
                        $lastError = $err;
                        usleep($retryDelayMs * ($attempt + 1) * 1000);
                        continue;
                    }
                    throw $err;
                }

                return $parsed;
            } catch (\Throwable $err) {
                $onError = $this->cfg['onError'] ?? null;
                $this->invokeHook(is_callable($onError) ? $onError : null, [
                    'method' => $method,
                    'url' => $url,
                    'error' => $err,
                ]);

                if ($err instanceof XBoardError) {
                    throw $err;
                }

                $timeoutErr = $this->timeoutError($err, $timeoutMs);
                if ($timeoutErr !== null) {
                    if ($attempt < $maxAttempts - 1) {
                        $lastError = $timeoutErr;
                        usleep($retryDelayMs * ($attempt + 1) * 1000);
                        continue;
                    }
                    throw $timeoutErr;
                }

                $connErr = new APIConnectionError([
                    'message' => $err->getMessage() !== '' ? $err->getMessage() : 'Network request failed',
                    'cause' => $err,
                ]);
                if ($attempt < $maxAttempts - 1) {
                    $lastError = $connErr;
                    usleep($retryDelayMs * ($attempt + 1) * 1000);
                    continue;
                }
                throw $connErr;
            }
        }

        if ($lastError instanceof \Throwable) {
            throw $lastError;
        }

        throw new APIConnectionError(['message' => 'Request failed']);
    }

    private function shouldRetry(int $status): bool
    {
        return $status === 408 || $status === 429 || $status >= 500;
    }

    private function timeoutError(\Throwable $err, int $timeoutMs): ?APITimeoutError
    {
        $isTimeout = $err instanceof ConnectException
            || $err instanceof RequestException
            || $err instanceof TransferException
            || $err instanceof GuzzleException;

        if (!$isTimeout) {
            return null;
        }

        $message = strtolower($err->getMessage());
        $looksLikeTimeout = str_contains($message, 'timed out')
            || str_contains($message, 'timeout')
            || str_contains($message, 'time out');

        if (!$looksLikeTimeout && !($err instanceof ConnectException && str_contains($message, 'cURL error 28'))) {
            return null;
        }

        return new APITimeoutError([
            'message' => 'Request timed out after ' . $timeoutMs . 'ms',
            'cause' => $err,
        ]);
    }

    /**
     * @param callable|null $hook
     * @param array<string, mixed> $info
     */
    private function invokeHook(?callable $hook, array $info): void
    {
        if ($hook === null) {
            return;
        }
        $hook($info);
    }

    private static function messageFromBody(mixed $body, string $fallback): string
    {
        if (is_array($body) && isset($body['message']) && is_string($body['message']) && $body['message'] !== '') {
            return $body['message'];
        }

        return $fallback;
    }
}
