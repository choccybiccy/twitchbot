<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Interfaces\CommandHandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Interfaces\FilesystemAwareHandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Interfaces\HandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Interfaces\LoopAwareHandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Traits\CanReadChat;
use Choccybiccy\TwitchBot\Handlers\Traits\CanSendChat;
use Choccybiccy\TwitchBot\Handlers\Traits\UsesFilesystem;
use Choccybiccy\TwitchBot\Handlers\Traits\UsesLoop;
use Choccybiccy\TwitchBot\Twitch\Client;
use League\Flysystem\FileNotFoundException;
use Ratchet\Client\WebSocket;
use React\EventLoop\TimerInterface;

/**
 * Class AnnouncementHandler.
 */
class AnnouncementHandler implements HandlerInterface, LoopAwareHandlerInterface, CommandHandlerInterface, FilesystemAwareHandlerInterface
{
    use UsesFilesystem, UsesLoop, CanReadChat, CanSendChat;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $announcements = [];

    /**
     * @var TimerInterface[]
     */
    protected $timers = [];

    /**
     * AnnouncementHandler constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function load(WebSocket $socket)
    {
        try {
            $announcements = json_decode($this->filesystem->read('announcements.json'), true);
            foreach ($announcements as $handle => $announcement) {
                $this->addAnnouncement($handle, $announcement['frequency'], $announcement['message'], $socket);
            }
        } catch (FileNotFoundException $e) {
            $this->save();
        }
    }

    /**
     * Save the announcements to the filesystem.
     */
    public function save()
    {
        return $this->filesystem->put('announcements.json', json_encode($this->announcements));
    }

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     */
    public function handle(string $message, WebSocket $socket)
    {
        $user = $this->client->getUser();
        if ($user['login'] !== $this->getUser($message)) {
            return;
        }

        $regex = '/^\!announcement (?<command>add|remove) (?<handle>[A-z0-9]+)(?: (?<frequency>[0-9]+) (?<message>.*))?/';
        if (preg_match($regex, $this->getMessage($message), $matches)) {
            $handle = $matches['handle'];
            $channel = $this->getChannel($message);
            switch ($matches['command'])
            {
                case 'add':
                    if ($this->addAnnouncement($handle, $matches['frequency'], $matches['message'], $socket)) {
                        $this->save();
                        $this->sendMessage('Announcement saved', $channel, $socket);
                    }
                    break;
                case 'remove':
                    if ($this->removeAnnouncement($handle)) {
                        $this->save();
                        $this->sendMessage('Announcement removed', $channel, $socket);
                    }
                    break;
            }
        }
    }

    /**
     * @return array
     */
    public function getAnnouncements(): array
    {
        return $this->announcements;
    }

    /**
     * @param string $handle
     * @param int $frequency
     * @param string $message
     * @param WebSocket $socket
     *
     * @return bool
     */
    public function addAnnouncement(string $handle, int $frequency, string $message, WebSocket $socket)
    {
        $this->announcements[$handle] = [
            'frequency' => $frequency,
            'message' => $message,
        ];
        if (!array_key_exists($handle, $this->timers)) {
            $timer = $this->loop->addPeriodicTimer($frequency*60, function () use ($socket, $message) {
                $this->sendMessage($message, getenv('TWITCHBOT_CHANNEL'), $socket);
            });
            $this->timers[$handle] = $timer;
        }
        return true;
    }

    /**
     * @param string $handle
     *
     * @return bool
     */
    public function removeAnnouncement(string $handle)
    {
        if (array_key_exists($handle, $this->announcements)) {
            unset($this->announcements[$handle]);
        }
        if (array_key_exists($handle, $this->timers)) {
            $this->loop->cancelTimer($this->timers[$handle]);
            unset($this->timers[$handle]);
        }
        return true;
    }

    /**
     * @return array
     */
    public function commandsSupported(): array
    {
        return ['announcement add <handle> <freq> <message>', 'announcement remove <handle>'];
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        $user = $this->client->getUser();
        if ($user['login'] !== $this->getUser($message)) {
            return false;
        }
        return preg_match('/^\!announcement (add|remove) ([A-z0-9]+)?/', $this->getMessage($message));
    }
}
