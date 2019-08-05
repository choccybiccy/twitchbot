<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Traits\CanLog;
use Choccybiccy\TwitchBot\Handlers\Traits\CanReadChat;
use Choccybiccy\TwitchBot\Handlers\Traits\CanSendChat;
use Choccybiccy\TwitchBot\Twitch\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Ratchet\Client\WebSocket;

/**
 * Class CustomCommandHandler.
 */
class CustomCommandHandler implements HandlerInterface, LoggerAwareInterface
{
    use CanSendChat, CanReadChat, CanLog;

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $commandsJsonPath;

    /**
     * CustomCommandHandler constructor.
     *
     * @param Client $client
     * @param string $commandsJsonPath
     */
    public function __construct(Client $client, string $commandsJsonPath = null)
    {
        $this->client = $client;
        if (!$commandsJsonPath) {
            $commandsJsonPath = realpath(__DIR__ . '/../../var/') . '/commands.json';
        }
        $this->commandsJsonPath = $commandsJsonPath;
        if (file_exists($commandsJsonPath)) {
            $commands = json_decode(file_get_contents($commandsJsonPath), true);
            if ($commands) {
                $this->commands = $commands;
            }
        } else {
            $this->save();
        }
    }

    /**
     * @param array $commands
     *
     * @return CustomCommandHandler
     */
    public function setCommands(array $commands): self
    {
        $this->commands = [];
        foreach($commands as $command => $output) {
            $this->addCommand($command, $output);
        }
        return $this;
    }

    /**
     * @param string $command
     * @param string $output
     *
     * @return CustomCommandHandler
     */
    public function addCommand(string $command, string $output): self
    {
        $this->commands[$command] = $output;
        return $this;
    }

    /**
     * @param string $command
     *
     * @return CustomCommandHandler
     */
    public function removeCommand(string $command): self
    {
        if (array_key_exists($command, $this->commands)) {
            unset($this->commands[$command]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return bool
     */
    protected function save(): bool
    {
        if ($handle = @fopen($this->commandsJsonPath, 'w')) {
            return fwrite($handle, json_encode($this->commands));
        }
        return false;
    }

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     * @throws GuzzleException
     */
    public function handle(string $message, WebSocket $socket)
    {
        $user = $this->getUser($message);

        $commands = implode('|', array_keys($this->commands));
        $inputMessage = trim($this->getMessage($message));
        if (preg_match('/^\!(addcommand|removecommand) (\w+)(?: (.*))?/', $inputMessage, $matches)) {
            $broadcaster = $this->client->getUser();
            //$moderators = $this->client->getModerators()->pluck('user_name')->toArray();
            $moderators = [];
            $admins = array_merge($moderators, [$broadcaster['login']]);
            if (in_array($user, $admins)) {
                switch($matches[1])
                {
                    case 'addcommand':
                        $this->addCommand($matches[2], trim($matches[3]));
                        $this->sendMessage($user . ': Command added', $this->getChannel($message), $socket);
                        $this->logger->debug('Added command ' . $matches[2], ['value' => trim($matches[3])]);
                        break;
                    case 'removecommand':
                        $this->removeCommand($matches[2]);
                        $this->sendMessage($user . ': Command removed', $this->getChannel($message), $socket);
                        $this->logger->debug('Removed command ' . $matches[2]);
                        break;
                }
                $this->save();
            }
        } elseif(count($this->commands) && preg_match('/^\!('.$commands.')$/', $inputMessage, $matches)) {
            $this->sendMessage($this->commands[$matches[1]], $this->getChannel($message), $socket);
        }
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        $commands = implode('|', array_merge(['addcommand', 'removecommand'], array_keys($this->commands)));
        return preg_match("/^\!(?:{$commands})/", $this->getMessage($message));
    }
}
