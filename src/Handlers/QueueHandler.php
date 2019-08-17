<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Interfaces\CommandHandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Traits\CanReadChat;
use Choccybiccy\TwitchBot\Handlers\Traits\CanSendChat;
use Choccybiccy\TwitchBot\Twitch\Client;
use Ratchet\Client\WebSocket;
use Tightenco\Collect\Support\Collection;

/**
 * Class QueueHandler.
 */
class QueueHandler extends AbstractHandler implements CommandHandlerInterface
{
    use CanReadChat, CanSendChat;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Collection
     */
    protected $queue;

    /**
     * QueueHandler constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->queue = new Collection();
    }

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     */
    public function handle(string $message, WebSocket $socket)
    {
        if (preg_match('/^\!queue(?:\s+(add|remove|next|me))?$/', $this->getMessage($message), $matches)) {
            $user = $this->getUser($message);
            $channel = $this->getChannel($message);
            if (count($matches) == 1) {
                if ($this->queue->count()) {
                    $response = $this->queue->slice(0, 5)->map(function ($user, $key) {
                        return '#' . ($key+1) . ': ' . $user;
                    })->join(', ');
                    if ($this->queue->count() > 5) {
                        $remaining = $this->queue->count() - 5;
                        $response.= sprintf(', and %d more', $remaining);
                    }
                    $this->sendMessage($response, $channel, $socket);
                }
            } else {
                switch ($matches[1]) {
                    case 'add':
                        if ($this->addToQueue($user)) {
                            $this->sendMessage(sprintf('@%s added you to the queue', $user), $channel, $socket);
                        }
                        break;
                    case 'remove':
                        if ($this->removeFromQueue($user)) {
                            $this->sendMessage(sprintf('@%s removed you from the queue', $user), $channel, $socket);
                        }
                        break;
                    case 'next':
                        if ($this->queue->count()) {
                            $next = $this->queue->shift();
                            $this->sendMessage(sprintf('@%s is up next', $next), $channel, $socket);
                        } else {
                            $this->sendMessage('There is nobody in the queue', $channel, $socket);
                        }
                        break;
                    case 'me':
                        $position = 0;
                        foreach ($this->queue as $item) {
                            $position++;
                            if ($item == $user) {
                                break;
                            }
                        }
                        if ($position > 0) {
                            $this->sendMessage(
                                sprintf('@%s you are #%d in the queue', $user, $position),
                                $channel,
                                $socket
                            );
                        } else {
                            $this->sendMessage(sprintf('@%s you are not in the queue', $user), $channel, $socket);
                        }
                        break;
                }
            }
        }
    }

    /**
     * @param string $user
     *
     * @return bool
     */
    public function addToQueue(string $user): bool
    {
        if (!$this->queue->contains($user)) {
            $this->queue->add($user);
            return true;
        }
        return false;
    }

    /**
     * @param string $user
     *
     * @return boolean
     */
    public function removeFromQueue(string $user): bool
    {
        if ($this->queue->contains($user)) {
            $this->queue->forget($this->queue->search($user));
            return true;
        }
        return false;
    }

    /**
     * @return Collection
     */
    public function getQueue(): Collection
    {
        return $this->queue;
    }

    /**
     * @return array
     */
    public function commandsSupported(): array
    {
        return ['queue', 'queue add', 'queue remove', 'queue next', 'queue me'];
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        if (preg_match('/^\!queue(?:\s+(add|remove|next|me))?$/', $this->getMessage($message), $matches)) {
            if (array_key_exists(1, $matches) && $matches[1] == 'next') {
                $user = $this->client->getUser();
                return $user && $user['login'] == $this->getUser($message);
            }
            return true;
        }
        return false;
    }
}
