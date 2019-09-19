<?php

namespace Choccybiccy\TwitchBot\Event;

use InvalidArgumentException;
use League\Event\AbstractEvent;
use Ratchet\Client\WebSocket;

/**
 * Class MessageEvent.
 */
class MessageEvent extends AbstractEvent
{
    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $message;
    
    /**
     * @var WebSocket
     */
    protected $socket;

    /**
     * MessageEvent construct.
     *
     * @param string $channel
     * @param string $user
     * @param string $message
     * @param WebSocket $socket
     */
    public function __construct(string $channel, string $user, string $message, WebSocket $socket)
    {
        $this->channel = $channel;
        $this->user = $user;
        $this->message = $message;
        $this->socket = $socket;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return WebSocket
     */
    public function getSocket(): WebSocket
    {
        return $this->socket;
    }

    /**
     * Create an instance of the MessageEvent from a raw message.
     *
     * @param string $message
     * @param WebSocket $socket
     * @return MessageEvent
     * 
     * @throws InvalidArgumentException
     */
    public static function createFromMessage(string $message, WebSocket $socket): MessageEvent
    {
        if (preg_match('/^:(.*)\!.*@.*\s+PRIVMSG\s+\#([A-z0-9_-]+)\s+\:(.*)/', $message, $matches)) {
            return new self($matches[2], $matches[1], $matches[3], $socket);
        }
        throw new InvalidArgumentException('Message is malformed');
    }
}
