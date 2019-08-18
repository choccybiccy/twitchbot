<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\DiceHandler;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ratchet\Client\WebSocket;
use spec\Choccybiccy\TwitchBot\Handlers\Traits\MessageMaker;

class DiceHandlerSpec extends ObjectBehavior
{
    use MessageMaker;

    public function it_should_handle_dice_commands()
    {
        $this->canHandle($this->makeInboundMessage('!d4'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!d6'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!d8'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!d10'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!d12'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!d20'))->shouldReturn(true);

        $this->canHandle($this->makeInboundMessage('!d5'))->shouldReturn(false);
    }

    public function it_should_roll_the_dice(WebSocket $socket)
    {
        $socket->send(Argument::that(function ($argument) {
            return preg_match('/Rolling the dice... \d+$/', $argument);
        }))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!d4'), $socket);
    }
}
