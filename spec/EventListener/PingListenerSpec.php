<?php

namespace spec\Choccybiccy\TwitchBot\EventListener;

use Choccybiccy\TwitchBot\Event\PingEvent;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;

class PingListenerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    public function it_should_handle_ping(PingEvent $event, WebSocket $socket)
    {
        $host = uniqid('host');
        $socket->send('PONG :' . $host)->shouldBeCalled();
        $event->getSocket()->willReturn($socket);
        $event->getHost()->willReturn($host);
        $this->handle($event);
    }
}
