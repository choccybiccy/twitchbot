<?php

namespace Choccybiccy\TwitchBot\Event;

use League\Event\AbstractEvent;
use Ratchet\Client\WebSocket;

/**
 * Class PingEvent.
 */
class PingEvent extends AbstractEvent
{
    /**
     * @var WebSocket
     */
    protected $socket;

    /**
     * @var string
     */
    protected $host;

    /**
     * PingEvent construct.
     *
     * @param WebSocket $socket
     * @param string $host
     */
    public function __construct(WebSocket $socket, string $host)
    {
        $this->socket = $socket;
        $this->host = $host;
    }

    /**
     * @return WebSocket
     */
    public function getSocket(): WebSocket
    {
        return $this->socket;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }
}
