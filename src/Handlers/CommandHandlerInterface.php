<?php

namespace Choccybiccy\TwitchBot\Handlers;

/**
 * Interface CommandHandlerInterface.
 */
interface CommandHandlerInterface
{
    /**
     * @return string[]
     */
    public function commandsSupported(): array;
}
