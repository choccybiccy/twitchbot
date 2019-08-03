<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\EchoHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ratchet\Client\WebSocket;

class EchoHandlerSpec extends ObjectBehavior
{
    public function it_should_handle_echo()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!echo hello')->shouldReturn(true);
    }

    public function it_should_not_handle_recursion()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!echo !echo hello')->shouldReturn(false);
    }

    public function it_should_echo(WebSocket $socket)
    {
        $socket->send('PRIVMSG #channel :hello')->shouldBeCalled();
        $this->handle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!echo hello', $socket);
    }
}
