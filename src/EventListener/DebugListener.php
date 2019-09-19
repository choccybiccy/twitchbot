<?php

namespace Choccybiccy\TwitchBot\EventListener;

use Choccybiccy\TwitchBot\Event\MessageEvent;
use League\Event\AbstractListener;
use League\Event\EventInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DebugListener.
 */
class DebugListener extends AbstractListener
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DebugListener construct.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Debug log all events emitted.
     *
     * @param EventInterface $event
     * @return void
     */
    public function handle(EventInterface $event)
    {
        $this->logger->debug('Event ' . $event->getName());
        if ($event instanceof MessageEvent) {
            $this->logger->debug(sprintf(
                '#%s: %s: %s', 
                $event->getChannel(), 
                $event->getUser(), 
                $event->getMessage()
            ));
        }
    }
}