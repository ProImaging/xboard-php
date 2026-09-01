<?php

declare(strict_types=1);

namespace XBoard\Errors;

final class APITimeoutError extends XBoardError
{
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
        $opts['code'] = $opts['code'] ?? 'api_timeout_error';
        parent::__construct($opts);
    }
}
