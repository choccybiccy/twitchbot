<?php

namespace Choccybiccy\TwitchBot;

use Choccybiccy\TwitchBot\Handlers\HandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;
use React\Promise\Promise as ReactClient;

/**
 * Class Application.
 */
class Application implements ApplicationInterface
{
    /**
     * @var string
     */
    protected $nickname;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $botToken;

    /**
     * @var ReactClient
     */
    protected $reactClient;

    /**
     * @var HandlerInterface[]
     */
    protected $handlers = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Application constructor.
     *
     * @param string $nickname
     * @param string $channel
     * @param string $botToken
     * @param ReactClient $reactClient
     * @param HandlerInterface[] $handlers
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $nickname,
        string $channel,
        string $botToken,
        ReactClient $reactClient,
        array $handlers,
        LoggerInterface $logger
    ) {
        $this->nickname = $nickname;
        $this->channel = $channel;
        $this->botToken = $botToken;
        $this->reactClient = $reactClient;
        $this->handlers = $handlers;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function run()
    {
        foreach ($this->handlers as $handler) {
            $this->logger->debug('Loaded handler: ' . get_class($handler));
        }
        $this->reactClient->then(function (WebSocket $socket) {
            $this->logger->info('Connection established');
            $this->socket = $socket;
            $socket->on('message', function ($message) use ($socket) {
                $this->logger->debug('< ' . $message);
                $this->handleMessage($message, $socket);
            });
            $socket->on('close', function ($code, $reason) {
                $this->logger->info('Connection closed', [
                    'code' => $code,
                    'reason' => $reason,
                ]);
            });
            $socket->send('PASS oauth:' . $this->botToken);
            $socket->send('NICK ' . $this->nickname);
            $socket->send('JOIN #' . $this->channel);
        });
    }

    /**
     * @param string $message
     * @param WebSocket $socket
     */
    protected function handleMessage(string $message, WebSocket $socket)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($message)) {
                if ($handler instanceof LoggerAwareInterface) {
                    $handler->setLogger($this->logger);
                }
                $handler->handle($message, $socket);
            }
        }
    }
}
