<?php

/*
 * This file is part of the Kuiper package.
 *
 * (c) Ye Wenbin <wenbinye@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace kuiper\resilience\retry\event;

use kuiper\resilience\retry\Retry;

/**
 * 重试结果为成功
 * Class RetryOnSuccess.
 */
class RetryOnSuccess
{
    public function __construct(
        private readonly Retry $retry,
        private readonly int $numOfAttempts,
        private readonly ?\Throwable $lastException)
    {
    }

    public function getRetry(): Retry
    {
        return $this->retry;
    }

    public function getNumOfAttempts(): int
    {
        return $this->numOfAttempts;
    }

    public function getLastException(): ?\Throwable
    {
        return $this->lastException;
    }
}
