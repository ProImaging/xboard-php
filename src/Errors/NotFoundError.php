<?php

declare(strict_types=1);

namespace XBoard\Errors;

final class NotFoundError extends XBoardError
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
        $opts['code'] = $opts['code'] ?? 'not_found';
        parent::__construct($opts);
    }
}
