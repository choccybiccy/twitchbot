<?php

namespace Choccybiccy\TwitchBot\Handlers\Interfaces;

use Ratchet\Client\WebSocket;

/**
 * Interface HandlerInterface.
 */
interface HandlerInterface
{
    /**
     * @param WebSocket $socket
     *
     * @return void
     */
    public function load(WebSocket $socket);

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed
     */
    public function handle(string $message, WebSocket $socket);

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool;
}
