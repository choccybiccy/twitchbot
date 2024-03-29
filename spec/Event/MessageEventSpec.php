<?php

namespace spec\Choccybiccy\TwitchBot\Event;

use PhpSpec\ObjectBehavior;
use Ratchet\Client\WebSocket;

class MessageEventSpec extends ObjectBehavior
{
    public function it_should_return_properties(WebSocket $socket)
    {
        $channel = uniqid('channel');
        $user = uniqid('user');
        $message = uniqid('message');
        $this->beConstructedWith($channel, $user, $message, $socket);
        $this->getChannel()->shouldReturn($channel);
        $this->getUser()->shouldReturn($user);
        $this->getMessage()->shouldReturn($message);
        $this->getSocket()->shouldReturn($socket);
    }

    public function it_should_create_from_message(WebSocket $socket)
    {
        $this->beConstructedWith('', '', '', $socket);
        $channel = uniqid('channel');
        $user = uniqid('user');
        $message = uniqid('Some message');
        $raw = sprintf(':%s!%s@%s.tmi.twitch.tv PRIVMSG #%s :%s', $user, $user, $user, $channel, $message);
        $obj = $this::createFromMessage($raw, $socket->getWrappedObject());
        $obj->getChannel()->shouldReturn($channel);
        $obj->getUser()->shouldReturn($user);
        $obj->getMessage()->shouldReturn($message);
        $obj->getSocket()->shouldReturn($socket);
    }
}
