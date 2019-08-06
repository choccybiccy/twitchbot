<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\CommandHandlerInterface;
use PhpSpec\ObjectBehavior;
use Ratchet\Client\WebSocket;

class ListCommandsHandlerSpec extends ObjectBehavior
{
    public function let(CommandHandlerInterface $handler)
    {
        $this->beConstructedWith([$handler]);
    }

    public function it_should_handle_listcommands()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!listcommands')->shouldReturn(true);
    }

    public function it_should_list_commands(CommandHandlerInterface $handler, WebSocket $socket)
    {
        $handler->commandsSupported()->willReturn(['abc', 'xyz']);
        $this->beConstructedWith([$handler]);
        $socket->send('PRIVMSG #channel :!abc, !xyz')->shouldBeCalled();
        $this->handle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!listcommands', $socket);
    }
}
