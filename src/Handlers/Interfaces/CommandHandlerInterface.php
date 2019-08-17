<?php

namespace Choccybiccy\TwitchBot\Handlers\Interfaces;

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
