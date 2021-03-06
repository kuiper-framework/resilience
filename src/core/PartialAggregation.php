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

namespace kuiper\resilience\core;

class PartialAggregation extends AbstractAggregation
{
    /**
     * @var Counter
     */
    private $epochSecond;

    /**
     * PartialAggregation constructor.
     */
    public function __construct(
        Counter $totalDuration,
        Counter $totalNumberOfSlowCalls,
        Counter $totalNumberOfSlowFailedCalls,
        Counter $totalNumberOfFailedCalls,
        Counter $totalNumberOfCalls,
        Counter $epochSecond)
    {
        parent::__construct($totalDuration, $totalNumberOfSlowCalls, $totalNumberOfSlowFailedCalls, $totalNumberOfFailedCalls, $totalNumberOfCalls);
        $this->epochSecond = $epochSecond;
    }

    public function getEpochSecond(): int
    {
        return $this->epochSecond->get();
    }

    public function setEpochSecond(int $epochSecond): void
    {
        $this->epochSecond->set($epochSecond);
    }
}
