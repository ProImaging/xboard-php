<?php

declare(strict_types=1);

namespace XBoard\Errors;

class XBoardError extends \RuntimeException
{
    public readonly ?int $statusCode;
    public readonly ?string $errorCode;
    public readonly ?string $requestId;
    public readonly mixed $raw;

    /**
     * @param array{
     *     message: string,
     *     statusCode?: int,
     *     code?: string,
     *     requestId?: string,
     *     raw?: mixed,
     *     cause?: \Throwable
     * } $opts
     */
    public function __construct(array $opts)
    {
        $previous = $opts['cause'] ?? null;
        parent::__construct($opts['message'], 0, $previous instanceof \Throwable ? $previous : null);
        $this->statusCode = $opts['statusCode'] ?? null;
        $this->errorCode = $opts['code'] ?? null;
        $this->requestId = $opts['requestId'] ?? null;
        $this->raw = $opts['raw'] ?? null;
    }

    public static function fromStatus(
        int $status,
        string $message,
        mixed $raw = null,
        ?string $requestId = null,
    ): self {
        $opts = [
            'message' => $message,
            'statusCode' => $status,
        ];
        if ($raw !== null) {
            $opts['raw'] = $raw;
        }
        if ($requestId !== null) {
            $opts['requestId'] = $requestId;
        }

        return match ($status) {
            401 => new AuthenticationError($opts),
            403 => new PermissionError($opts),
            404 => new NotFoundError($opts),
            429 => new RateLimitError($opts),
            default => new APIError($opts),
        };
    }
}
