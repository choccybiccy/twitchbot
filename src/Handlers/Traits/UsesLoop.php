<?php

namespace Choccybiccy\TwitchBot\Handlers\Traits;

use React\EventLoop\LoopInterface;

/**
 * Trait UsesLoop.
 */
trait UsesLoop
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop)
    {
        $this->loop = $loop;
    }
}