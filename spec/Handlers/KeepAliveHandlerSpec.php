<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;

class KeepAliveHandlerSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public function it_should_handle_ping()
    {
        $this->canHandle('PING blah')->shouldReturn(true);
    }

    public function it_should_pong(WebSocket $socket)
    {
        $server = 'some.server.com';
        $socket->send('PONG :' . $server)->shouldBeCalled();
        $this->handle('PING :' . $server, $socket);
    }
}
