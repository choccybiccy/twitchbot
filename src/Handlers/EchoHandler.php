<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Traits\CanReadChat;
use Choccybiccy\TwitchBot\Handlers\Traits\CanSendChat;
use Ratchet\Client\WebSocket;

/**
 * Class EchoHandler.
 */
class EchoHandler implements HandlerInterface
{
    use CanReadChat, CanSendChat;

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     */
    public function handle(string $message, WebSocket $socket)
    {
        if ($parts = $this->getCommandParts($message)) {
            $this->sendMessage($parts[1], $this->getChannel($message), $socket);
        }
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        if ($parts = $this->getCommandParts($message)) {
            return substr($parts[1], 0, 1) !== '!';
        }
        return false;
    }

    /**
     * @param string $message
     *
     * @return array|null
     */
    protected function getCommandParts(string $message): ?array
    {
        if (preg_match('/^\!echo (.*)/', $this->getMessage($message), $matches)) {
            return $matches;
        }
        return null;
    }
}
