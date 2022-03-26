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

namespace kuiper\resilience\retry;

use kuiper\helper\Arrays;
use kuiper\resilience\core\Clock;
use kuiper\resilience\core\CounterFactory;
use kuiper\resilience\core\SimpleClock;
use kuiper\swoole\pool\ConnectionProxyGenerator;
use kuiper\swoole\pool\PoolFactoryInterface;
use kuiper\swoole\pool\PoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class RetryFactoryImpl implements RetryFactory
{
    /**
     * @var PoolFactoryInterface
     */
    private $poolFactory;
    /**
     * @var CounterFactory
     */
    private $counterFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var array
     */
    private $options;

    /**
     * @var PoolInterface[]
     */
    private $retryPoolList;

    /**
     * @var Retry[]
     */
    private $retryList;

    /**
     * @var Clock
     */
    private $clock;

    /**
     * @var string|null
     */
    private $proxyClass;

    public function __construct(PoolFactoryInterface $poolFactory, CounterFactory $counterFactory, EventDispatcherInterface $eventDispatcher, array $options = null)
    {
        $this->poolFactory = $poolFactory;
        $this->counterFactory = $counterFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->clock = new SimpleClock();
        $this->options = $options ?? [];
    }

    private function getProxyClass(): string
    {
        if (null === $this->proxyClass) {
            $generator = new ConnectionProxyGenerator();
            $result = $generator->generate(Retry::class);
            $result->eval();
            $this->proxyClass = $result->getClassName();
        }

        return $this->proxyClass;
    }

    /**
     * @return Retry[]
     */
    public function getRetryList(): array
    {
        return Arrays::flatten(Arrays::pull($this->retryPoolList, 'connections'));
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $name): Retry
    {
        if (!isset($this->retryList[$name])) {
            $this->retryPoolList[$name] = $this->poolFactory->create('retry_'.$name, function () use ($name): Retry {
                return new RetryImpl(
                    $name,
                    $this->createConfig($name),
                    $this->clock,
                    $this->counterFactory,
                    $this->eventDispatcher
                );
            });
            $class = $this->getProxyClass();
            $this->retryList[$name] = new $class($this->retryPoolList[$name]);
        }

        return $this->retryList[$name];
    }

    private function createConfig(string $name): RetryConfig
    {
        if (!isset($this->options[$name])) {
            [$name] = explode('::', $name);
        }
        $options = Arrays::filter(array_merge($this->options['default'] ?? [], $this->options[$name] ?? []));
        if (empty($options)) {
            return RetryConfig::ofDefaults();
        }
        $configBuilder = RetryConfig::builder();
        if (isset($options['interval_function'])
            && 'exponential_backoff' === $options['interval_function']) {
            $options['interval_function'] = static function (int $numOfAttempts, int $waitDuration) {
                $countdown = $waitDuration * (2 ** $numOfAttempts);

                return min(10000, $countdown);
            };
        }
        Arrays::assign($configBuilder, $options);

        return $configBuilder->build();
    }
}
