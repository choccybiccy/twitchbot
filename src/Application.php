<?php

namespace Choccybiccy\TwitchBot;

use Choccybiccy\TwitchBot\Event\MessageEvent;
use Choccybiccy\TwitchBot\Event\PingEvent;
use InvalidArgumentException;
use League\Event\Emitter;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;
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
     * @var string[]
     */
    protected $channels;

    /**
     * @var string
     */
    protected $botToken;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var ReactClient
     */
    protected $reactClient;

    /**
     * @var Emitter
     */
    protected $events;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Application constructor.
     *
     * @param string $nickname
     * @param string[] $channels
     * @param string $botToken
     * @param LoopInterface $loop
     * @param ReactClient $reactClient
     * @param Emitter $events
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $nickname,
        array $channels,
        string $botToken,
        LoopInterface $loop,
        ReactClient $reactClient,
        Emitter $events,
        LoggerInterface $logger
    ) {
        $this->nickname = $nickname;
        $this->channels = $channels;
        $this->botToken = $botToken;
        $this->loop = $loop;
        $this->reactClient = $reactClient;
        $this->events = $events;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->reactClient->then(function (WebSocket $socket) {
            $this->logger->info('Connection established');
            $this->socket = $socket;
            $socket->on('message', function ($message) use ($socket) {
                $this->logger->debug('< ' . $message);
                $this->emitEvents($message, $socket);
            });
            $socket->on('close', function ($code, $reason) {
                $this->logger->info('Connection closed', [
                    'code' => $code,
                    'reason' => $reason,
                ]);
            });
            $socket->send(sprintf('PASS oauth:%s', $this->botToken));
            $socket->send(sprintf('NICK %s', $this->nickname));
            foreach ($this->channels as $channel) {
                $socket->send(sprintf('JOIN #%s', $channel));
            }
        });
        $this->loop->run();
    }

    /**
     * Emit events message on the message received.
     *
     * @param string $message
     * @param WebSocket $socket
     * @return void
     */
    protected function emitEvents(string $message, WebSocket $socket)
    {
        if (preg_match('/^PING\s+:(.*)/', $message, $matches)) {
            $this->events->emit(new PingEvent($socket, $matches[1]));
        }

        try {
            $message = MessageEvent::createFromMessage($message, $socket);
            $this->events->emit($message);
        } catch(InvalidArgumentException $e) {
        }
    }
}
