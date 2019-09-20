<?php

namespace spec\Choccybiccy\TwitchBot\Event;

use PhpSpec\ObjectBehavior;
use Ratchet\Client\WebSocket;

class PingEventSpec extends ObjectBehavior
{
    public function it_should_return_properties(WebSocket $socket)
    {
        $host = uniqid('host');
        $this->beConstructedWith($socket, $host);
        $this->getSocket()->shouldReturn($socket);
        $this->getHost()->shouldReturn($host);
    }
}
