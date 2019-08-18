<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Interfaces\CommandHandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Interfaces\HandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Traits\CanReadChat;
use Choccybiccy\TwitchBot\Handlers\Traits\CanSendChat;
use Ratchet\Client\WebSocket;

/**
 * Class DiceHandler.
 */
class DiceHandler implements HandlerInterface, CommandHandlerInterface
{
    use CanSendChat, CanReadChat;

    /**
     * @inheritDoc
     */
    public function load(WebSocket $socket)
    {
    }

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     */
    public function handle(string $message, WebSocket $socket)
    {
        if (preg_match('/^\!d(4|6|8|10|12|20)$/', $this->getMessage($message), $matches)) {
            $number = mt_rand(1, (int) $matches[1]);
            $this->sendMessage(sprintf('Rolling the dice... %d', $number), $this->getChannel($message), $socket);
        }
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        return preg_match('/^\!d(4|6|8|10|12|20)$/', $this->getMessage($message));
    }

    /**
     * @return array
     */
    public function commandsSupported(): array
    {
        return ['d4', 'd6', 'd8', 'd10', 'd12', 'd20'];
    }
}
