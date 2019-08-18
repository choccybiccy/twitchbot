<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\AnnouncementHandler;
use Choccybiccy\TwitchBot\Twitch\Client;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use spec\Choccybiccy\TwitchBot\Handlers\Traits\MessageMaker;

class AnnouncementHandlerSpec extends ObjectBehavior
{
    use MessageMaker;

    public function let(Client $client, FilesystemInterface $filesystem, LoopInterface $loop, LoggerInterface $logger)
    {
        $this->beConstructedWith($client);
        $this->setFilesystem($filesystem);
        $this->setLoop($loop);
        $this->setLogger($logger);
    }

    public function it_should_not_handle_announcement_if_not_admin(Client $client)
    {
        $admin = uniqid('admin');
        $viewer = uniqid('viewer');
        $client->getUser()->willReturn([
            'login' => $admin,
        ]);

        $this->canHandle(
            $this->makeInboundMessage('!announcement add test 60 This is a message', $viewer)
        )->shouldReturn(false);
        $this->canHandle(
            $this->makeInboundMessage('!announcement remove test', $viewer)
        )->shouldReturn(false);
    }

    public function it_should_handle_announcement_if_admin(Client $client)
    {
        $admin = uniqid('admin');
        $client->getUser()->willReturn([
            'login' => $admin,
        ]);
        $this->canHandle(
            $this->makeInboundMessage('!announcement add test 60 This is a message', $admin)
        )->shouldReturn(true);
        $this->canHandle(
            $this->makeInboundMessage('!announcement remove test', $admin)
        )->shouldReturn(true);
    }

    public function it_should_add_announcement(
        Client $client,
        FilesystemInterface $filesystem,
        LoopInterface $loop,
        WebSocket $socket
    ) {
        $this->setFilesystem($filesystem);
        $this->setLoop($loop);

        $admin = uniqid('admin');
        $message = 'This is a test message';

        $client->getUser()->willReturn(['login' => $admin]);

        $loop->addPeriodicTimer(60*60, Argument::any())->shouldBeCalled();
        $filesystem->put('announcements.json', json_encode([
            'test' => [
                'frequency' => 60,
                'message' => $message,
            ]
        ]))->shouldBeCalled();

        $socket->send($this->makeOuboundMessage('Announcement saved'))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!announcement add test 60 ' . $message, $admin), $socket);
        $this->getAnnouncements()->shouldHaveKeyWithValue('test', ['frequency' => 60, 'message' => $message]);
    }

    public function it_should_remove_announcement(
        Client $client,
        FilesystemInterface $filesystem,
        LoopInterface $loop,
        TimerInterface $timer,
        WebSocket $socket
    ) {
        $admin = uniqid('admin');
        $client->getUser()->willReturn(['login' => $admin]);

        $loop->addPeriodicTimer(60*60, Argument::any())->willReturn($timer);
        $this->addAnnouncement('test', 60, 'testing', $socket);

        $this->getAnnouncements()->shouldHaveKey('test');

        $loop->cancelTimer($timer)->shouldBeCalled();

        $socket->send($this->makeOuboundMessage('Announcement removed'))->shouldBeCalled();
        $this->handle($this->makeInboundMessage('!announcement remove test', $admin), $socket);
        $this->getAnnouncements()->shouldNotHaveKey('test');
    }
}
