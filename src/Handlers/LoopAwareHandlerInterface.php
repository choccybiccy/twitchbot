<?php

namespace Choccybiccy\TwitchBot\Handlers;

use React\EventLoop\LoopInterface;

/**
 * Interface LoopAwareHandlerInterface.
 */
interface LoopAwareHandlerInterface
{
    /**
     * @param LoopInterface $loop
     *
     * @return LoopAwareHandlerInterface
     */
    public function setLoop(LoopInterface $loop): self;

    /**
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface;
}
