<?php
/**
 * Class Channel
 * @Auhtor zp
 * @Time 2021/9/28 19:41
 */

namespace App\Utility\Database\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Engine\Channel as CoChannel;
use Hyperf\Utils\Coroutine;

class Channel
{
    protected $size;

    /**
     * @var CoChannel
     */
    protected $channel;

    /**
     * @var \SplQueue
     */
    protected $queue;

    public function __construct(int $size)
    {
        $this->size = $size;
        $this->channel = new CoChannel($size);
        $this->queue = new \SplQueue();
    }

    /**
     * @return ConnectionInterface|false
     */
    public function pop(float $timeout)
    {
        if ($this->isCoroutine()) {
            return $this->channel->pop($timeout);
        }
        return $this->queue->shift();
    }

    /**
     * @param ConnectionInterface $data
     * @return bool
     */
    public function push($data)
    {
        if ($this->isCoroutine()) {
            return $this->channel->push($data);
        }
        $this->queue->push($data);
        return true;
    }

    public function length(): int
    {
        if ($this->isCoroutine()) {
            return $this->channel->getLength();
        }
        return $this->queue->count();
    }

    protected function isCoroutine(): bool
    {
        return Coroutine::id() > 0;
    }
}