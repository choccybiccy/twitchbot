<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Interfaces\HandlerInterface;
use Ratchet\Client\WebSocket;

/**
 * Class AbstractHandler.
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * Load the handler.
     *
     * @param WebSocket $socket
     */
    public function load(WebSocket $socket)
    {
    }
}