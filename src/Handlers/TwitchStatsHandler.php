<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Choccybiccy\TwitchBot\Handlers\Traits\CanReadChat;
use Choccybiccy\TwitchBot\Handlers\Traits\CanSendChat;
use Choccybiccy\TwitchBot\Twitch\Client;
use GuzzleHttp\Exception\GuzzleException;
use Ratchet\Client\WebSocket;

/**
 * Class TwitchStatsHandler.
 */
class TwitchStatsHandler implements HandlerInterface, CommandHandlerInterface
{
    use CanReadChat, CanSendChat;

    /**
     * @var array
     */
    protected $commands = [
        'followers',
        'uptime',
        'viewers',
    ];

    /**
     * @var string
     */
    protected $nickname;

    /**
     * @var Client
     */
    protected $twitchClient;

    /**
     * TwitchStatsHandler constructor.
     *
     * @param string $nickname
     * @param Client $twitchClient
     */
    public function __construct(string $nickname, Client $twitchClient)
    {
        $this->nickname = $nickname;
        $this->twitchClient = $twitchClient;
    }

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     */
    public function handle(string $message, WebSocket $socket)
    {
        $commands = implode('|', $this->commands);
        if (preg_match("/^\!({$commands})/", $this->getMessage($message), $matches)) {
            $method = 'handle' . ucfirst($matches[1]);
            if (method_exists($this, $method)) {
                $channel = $this->getChannel($message);
                $this->{$method}($channel, $socket);
            }
        }
    }

    /**
     * @param string $channel
     * @param WebSocket $socket
     *
     * @throws GuzzleException
     */
    protected function handleFollowers(string $channel, WebSocket $socket)
    {
        $this->sendMessage(
            sprintf('%s has %d followers', $this->nickname, $this->twitchClient->getFollowerCount()),
            $channel,
            $socket
        );
    }

    /**
     * @param string $channel
     * @param WebSocket $socket
     *
     * @throws GuzzleException
     */
    protected function handleUptime(string $channel, WebSocket $socket)
    {
        $streamData = $this->twitchClient->getStream();
        if ($streamData && $streamData['type'] == 'live') {
            $startedAt = new Carbon($streamData['started_at']);
            $uptime = $startedAt->diffForHumans(['parts' => 3, 'join' => true], CarbonInterface::DIFF_ABSOLUTE);
            $this->sendMessage(
                sprintf('%s has been streaming for %s', $this->nickname, $uptime),
                $channel,
                $socket
            );
        } else {
            $this->sendMessage(
                sprintf('%s is not currently streaming', $this->nickname),
                $channel,
                $socket
            );
        }
    }

    /**
     * @param string $channel
     * @param WebSocket $socket
     *
     * @throws GuzzleException
     */
    protected function handleViewers(string $channel, WebSocket $socket)
    {
        $streamData = $this->twitchClient->getStream();
        if ($streamData && $streamData['type'] == 'live') {
            $this->sendMessage(
                sprintf('There are currently %d viewers', $streamData['viewer_count']),
                $channel,
                $socket
            );
        } else {
            $this->sendMessage(
                sprintf('%s is not currently streaming', $this->nickname),
                $channel,
                $socket
            );
        }
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        $commands = implode('|', $this->commands);
        return preg_match("/^\!(?:{$commands})/", $this->getMessage($message));
    }

    /**
     * @return array
     */
    public function commandsSupported(): array
    {
        return $this->commands;
    }
}
