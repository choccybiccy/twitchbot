<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Interfaces\CommandHandlerInterface;
use Choccybiccy\TwitchBot\Handlers\Interfaces\HandlerInterface;
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
class CustomCommandHandler implements HandlerInterface, LoggerAwareInterface, CommandHandlerInterface
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
     * @var string
     */
    protected $defaultCommandsFile = 'commands.json';

    /**
     * @var string
     */
    protected $addCommandString = 'addcommand';

    /**
     * @var string
     */
    protected $removeCommandString = 'removecommand';

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
            $commandsJsonPath = realpath(__DIR__ . '/../../var/') . '/' . $this->defaultCommandsFile;
        }
        $this->commandsJsonPath = $commandsJsonPath;
    }

    /**
     * @inheritDoc
     */
    public function load(WebSocket $socket)
    {
        if (file_exists($this->commandsJsonPath)) {
            $commands = json_decode(file_get_contents($this->commandsJsonPath), true);
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

        $editCommands = implode('|', [$this->addCommandString, $this->removeCommandString]);
        if (preg_match('/^\!('.$editCommands.') (\w+)(?: (.*))?/', $inputMessage, $matches)) {
            $broadcaster = $this->client->getUser();
            //$moderators = $this->client->getModerators()->pluck('user_name')->toArray();
            $moderators = [];
            $admins = array_merge($moderators, [$broadcaster['login']]);
            if (in_array($user, $admins)) {
                switch($matches[1])
                {
                    case $this->addCommandString:
                        $this->addCommand($matches[2], trim($matches[3]));
                        $this->logger->debug('Added command ' . $matches[2], ['value' => trim($matches[3])]);
                        break;
                    case $this->removeCommandString:
                        $this->removeCommand($matches[2]);
                        $this->logger->debug('Removed command ' . $matches[2]);
                        break;
                }
                $this->save();
            }
        } elseif(count($this->commands) && preg_match('/^\!('.$commands.')$/', $inputMessage, $matches)) {
            $this->sendMessage($this->getMessageForOutput($matches[1]), $this->getChannel($message), $socket);
        }
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getMessageForOutput(string $key): string
    {
        return $this->commands[$key];
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        $editCommands = [$this->addCommandString, $this->removeCommandString];
        $commands = implode('|', array_merge($editCommands, array_keys($this->commands)));
        return preg_match("/^\!(?:{$commands})/", $this->getMessage($message));
    }

    /**
     * @return array
     */
    public function commandsSupported(): array
    {
        return array_keys($this->commands);
    }
}
