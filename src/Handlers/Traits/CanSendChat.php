<?php

namespace Choccybiccy\TwitchBot\Handlers\Traits;

use Ratchet\Client\WebSocket;

/**
 * Trait CanSendChat.
 */
trait CanSendChat
{
    /**
     * @param string $message
     * @param string $channel
     * @param WebSocket $socket
     */
    public function sendMessage(string $message, string $channel, WebSocket $socket)
    {
        $socket->send(sprintf('PRIVMSG #%s :%s', $channel, $message));
    }
}
