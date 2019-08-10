<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Twitch\Client;
use PhpSpec\ObjectBehavior;
use Ratchet\Client\WebSocket;
use spec\Choccybiccy\TwitchBot\Handlers\Traits\MessageMaker;

class QueueHandlerSpec extends ObjectBehavior
{
    use MessageMaker;

    public function let(Client $client)
    {
        $this->beConstructedWith($client);
    }

    public function it_can_handle_queue()
    {
        $this->canHandle($this->makeInboundMessage('!queue'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!queue add'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!queue remove'))->shouldReturn(true);
        $this->canHandle($this->makeInboundMessage('!queue me'))->shouldReturn(true);
    }

    public function it_should_handle_queue_next_if_admin(Client $client)
    {
        $user = ['login' => uniqid('broadcaster_')];
        $client->getUser()->willReturn($user);
        $this->canHandle($this->makeInboundMessage('!queue next'))->shouldReturn(false);
        $this->canHandle($this->makeInboundMessage('!queue next', $user['login']))->shouldReturn(true);
    }

    public function it_can_list_commands()
    {
        $this->commandsSupported()->shouldReturn(['queue', 'queue add', 'queue remove', 'queue next', 'queue me']);
    }

    public function it_can_show_queue(WebSocket $socket)
    {
        $users = [];
        foreach(range(1, 3) as $key) {
            $users[$key] = uniqid('viewer');
            $this->addToQueue($users[$key]);
        }
        $output = sprintf('#%d: %s, #%d: %s, #%d: %s',
            1, $users[1],
            2, $users[2],
            3, $users[3]
        );
        $socket->send($this->makeOuboundMessage($output))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue'), $socket);
    }

    public function it_should_show_how_many_more_in_queue_if_too_long(WebSocket $socket)
    {
        $users = [];
        foreach(range(1, 10) as $key) {
            $users[$key] = uniqid('viewer');
            $this->addToQueue($users[$key]);
        }
        $output = sprintf('#%d: %s, #%d: %s, #%d: %s, #%d: %s, #%d: %s, and 5 more',
            1, $users[1],
            2, $users[2],
            3, $users[3],
            4, $users[4],
            5, $users[5]
        );
        $socket->send($this->makeOuboundMessage($output))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue'), $socket);
    }

    public function it_can_add_to_queue(WebSocket $socket)
    {
        $name = uniqid('viewer');
        $socket->send($this->makeOuboundMessage(sprintf('@%s added you to the queue', $name)))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue add', $name), $socket);
    }

    public function it_should_prevent_duplicates(WebSocket $socket)
    {
        $name = uniqid('viewer');
        $socket->send($this->makeOuboundMessage(sprintf('@%s added you to the queue', $name)))->shouldBeCalledOnce();
        $this->handle($this->makeInboundMessage('!queue add', $name), $socket);
        $this->handle($this->makeInboundMessage('!queue add', $name), $socket);
    }

    public function it_should_show_queue_position(WebSocket $socket)
    {
        $first = uniqid('viewer');
        $second = uniqid('viewer');
        $third = uniqid('viewer');
        $fourth = uniqid('viewer');
        $this->addToQueue($first);
        $this->addToQueue($second);
        $this->addToQueue($third);
        $this->addToQueue($fourth);
        $socket->send($this->makeOuboundMessage(sprintf('@%s you are #3 in the queue', $third)))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue me', $third), $socket);
    }

    public function it_should_show_if_not_in_queue(WebSocket $socket)
    {
        $user = uniqid('viewer');
        $socket->send($this->makeOuboundMessage(sprintf('@%s you are not in the queue', $user)))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue me', $user), $socket);
    }

    public function it_should_remove_from_queue(WebSocket $socket)
    {
        $user = uniqid('viewer');
        $this->addToQueue($user);
        $socket->send($this->makeOuboundMessage(sprintf('@%s removed you from the queue', $user)))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue remove', $user), $socket);
        $this->getQueue()->contains($user)->shouldReturn(false);
    }

    public function it_should_show_who_is_next(WebSocket $socket)
    {
        $users = [];
        foreach(range(1, 5) as $key) {
            $users[$key] = uniqid('viewer');
            $this->addToQueue($users[$key]);
        }
        $this->getQueue()->count()->shouldReturn(5);
        $socket->send($this->makeOuboundMessage(sprintf('@%s is up next', $users[1])))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue next'), $socket);
        $this->getQueue()->contains($users[1])->shouldReturn(false);
        $this->getQueue()->count()->shouldReturn(4);
    }

    public function it_should_show_queue_is_empty_on_next(WebSocket $socket)
    {
        $socket->send($this->makeOuboundMessage('There is nobody in the queue'))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!queue next'), $socket);
    }
}
