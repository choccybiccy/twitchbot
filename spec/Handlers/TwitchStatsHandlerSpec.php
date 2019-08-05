<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Choccybiccy\TwitchBot\Twitch\Client;
use PhpSpec\ObjectBehavior;
use Ratchet\Client\WebSocket;

class TwitchStatsHandlerSpec extends ObjectBehavior
{
    protected $nickname = 'someNickname';

    public function let(Client $client)
    {
        $this->beConstructedWith($this->nickname, $client);
    }

    public function it_should_handle_followers()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!followers')->shouldReturn(true);
    }

    public function it_should_return_followers(Client $client, WebSocket $socket)
    {
        $followers = mt_rand(1000, 9999);
        $client->getFollowerCount()->willReturn($followers);
        $socket->send(sprintf('PRIVMSG #channel :%s has %d followers', $this->nickname, $followers))->shouldBeCalled();
        $this->handle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!followers', $socket);
    }

    public function it_should_handle_uptime()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!uptime')->shouldReturn(true);
    }

    public function it_should_return_uptime(Client $client, WebSocket $socket)
    {
        $startTime = (new Carbon())->subHours(mt_rand(1, 3))->subMinutes(mt_rand(0, 60))->subSeconds(mt_rand(0, 60));
        $streamData = [
            'started_at' => $startTime->toIso8601ZuluString(),
            'type' => 'live',
        ];
        $client->getStream()->willReturn($streamData);
        $uptime = $startTime->diffForHumans(['parts' => 3, 'join' => true], CarbonInterface::DIFF_ABSOLUTE);

        $socket->send(sprintf('PRIVMSG #channel :%s has been streaming for %s', $this->nickname, $uptime))
            ->shouldBeCalled();
        $this->handle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!uptime', $socket);
    }

    public function it_should_handle_uptime_when_offline(Client $client, WebSocket $socket)
    {
        $client->getStream()->willReturn([]);
        $socket->send(sprintf('PRIVMSG #channel :%s is not currently streaming', $this->nickname))
            ->shouldBeCalled();
        $this->handle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!uptime', $socket);
    }

    public function it_should_handle_viewers()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!viewers')->shouldReturn(true);
    }

    public function it_should_return_viewers(Client $client, WebSocket $socket)
    {
        $streamData = [
            'viewer_count' => mt_rand(1000, 9999),
            'type' => 'live',
        ];
        $client->getStream()->willReturn($streamData);
        $socket->send(sprintf('PRIVMSG #channel :There are currently %d viewers', $streamData['viewer_count']))
            ->shouldBeCalled();
        $this->handle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!viewers', $socket);
    }

    public function it_should_handle_viewers_when_offline(Client $client, WebSocket $socket)
    {
        $client->getStream()->willReturn([]);
        $socket->send(sprintf('PRIVMSG #channel :%s is not currently streaming', $this->nickname))
            ->shouldBeCalled();
        $this->handle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!viewers', $socket);
    }
}
