<?php

namespace Choccybiccy\TwitchBot\EventListener;

use Choccybiccy\TwitchBot\Event\PingEvent;
use League\Event\AbstractListener;
use League\Event\EventInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PingListener.
 */
class PingListener extends AbstractListener
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * PingListener construct.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle the ping event.
     *
     * @param PingEvent|EventInterface $event
     * @return void
     */
    public function handle(EventInterface $event)
    {
        /** @var $event PingEvent */
        $event->getSocket()->send('PONG :' . $event->getHost());
        $this->logger->debug('PING? PONG!');
    }
}