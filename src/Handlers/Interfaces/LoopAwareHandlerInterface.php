<?php

namespace Choccybiccy\TwitchBot\Handlers\Interfaces;

use React\EventLoop\LoopInterface;

/**
 * Interface LoopAwareHandlerInterface.
 */
interface LoopAwareHandlerInterface
{
    /**
     * @param LoopInterface $loop
     */
    public function setLoop(LoopInterface $loop);

    /**
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface;
}
