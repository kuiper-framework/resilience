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

class RedisCounter implements Counter
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var string
     */
    private $key;

    /**
     * RedisCounter constructor.
     *
     * @param \Redis $redis
     */
    public function __construct($redis, string $key)
    {
        $this->redis = $redis;
        $this->key = $key;
    }

    public function increment(int $value = 1): int
    {
        return $this->redis->incrBy($this->key, $value);
    }

    public function get(): int
    {
        return (int) $this->redis->get($this->key);
    }

    public function set(int $value): void
    {
        $this->redis->set($this->key, $value);
    }

    public function decrement(int $value = 1): int
    {
        return $this->redis->decrBy($this->key, $value);
    }
}
