<?php

namespace spec\Choccybiccy\TwitchBot\Event;

use Choccybiccy\TwitchBot\Event\MessageEvent;
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
        $channel = uniqid('channel');
        $user = uniqid('user');
        $message = uniqid('Some message');
        $raw = sprintf(':%s!%s@%s.tmi.twitch.tv PRIVMSG #%s :%s', $user, $user, $channel, $channel, $message);
        $message = MessageEvent::createFromMessage($raw, $socket->getWrappedObject());
    }
}
