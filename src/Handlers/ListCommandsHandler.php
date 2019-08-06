<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Handlers\Traits\CanReadChat;
use Choccybiccy\TwitchBot\Handlers\Traits\CanSendChat;
use Ratchet\Client\WebSocket;
use Tightenco\Collect\Support\Collection;

/**
 * Class ListCommandsHandler.
 */
class ListCommandsHandler implements HandlerInterface
{
    use CanReadChat, CanSendChat;

    /**
     * @var CommandHandlerInterface[]
     */
    protected $handlers = [];

    /**
     * ListCommandsHandler constructor.
     *
     * @param CommandHandlerInterface[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param string $message
     * @param WebSocket $socket
     *
     * @return mixed|void
     */
    public function handle(string $message, WebSocket $socket)
    {
        $commands = new Collection();
        foreach ($this->handlers as $handler) {
            if ($handler instanceof CommandHandlerInterface) {
                $commands = $commands->merge($handler->commandsSupported());
            }
        }
        $output = [];
        foreach ($commands as $command) {
            $output[] = '!' . $command;
        }
        $this->sendMessage(trim(implode(', ', $output)), $this->getChannel($message), $socket);
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function canHandle(string $message): bool
    {
        return $this->getMessage($message) == '!listcommands';
    }
}
