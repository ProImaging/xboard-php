<?php

declare(strict_types=1);

namespace XBoard;

/**
 * Partner board type sent as `boardType` (`shared` | `private`).
 */
enum BoardType: string
{
    case Shared = 'shared';
    case Private = 'private';
}
