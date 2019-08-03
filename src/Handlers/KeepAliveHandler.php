<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Traits\CanLog;
use Psr\Log\LoggerAwareInterface;
use Ratchet\Client\WebSocket;

/**
 * Class KeepAliveHandler.
 */
class KeepAliveHandler implements HandlerInterface, LoggerAwareInterface
{
    use CanLog;

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     */
    public function handle(string $message, WebSocket $socket)
    {
        if (preg_match('/^PING\s+(.*)/', $message, $matches)) {
            $this->logger->debug('PING? PONG!');
            $socket->send('PONG ' . $matches[1]);
        }
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        return preg_match('/^PING/', $message);
    }
}