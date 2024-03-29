<?php

namespace Choccybiccy\TwitchBot\Provider;

use Choccybiccy\TwitchBot\Event\PingEvent;
use Choccybiccy\TwitchBot\EventListener\DebugListener;
use Choccybiccy\TwitchBot\EventListener\PingListener;
use League\Container\ContainerAwareInterface;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerInterface;
use League\Event\ListenerProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EventProvider.
 */
class EventProvider implements ListenerProviderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * EventProvider construct.
     *
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * Listeners.
     *
     * @param ListenerAcceptorInterface $listenerAcceptor
     * @return void
     */
    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor)
    {
        $logger = $this->container->get(LoggerInterface::class);

        $listenerAcceptor->addListener(
            PingEvent::class, 
            new PingListener($this->container->get(LoggerInterface::class))
        );
        $logger->info('Loaded ' . PingListener::class);

        if (array_key_exists('listeners', $this->config) 
            && is_array($this->config['listeners'])
        ) {
            foreach ($this->config['listeners'] as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $className = $listener;
                    if ($this->container->has($listener)) {
                        $listener = $this->container->get($listener);
                    } elseif(class_exists($listener)) {
                        $listener = new $listener;
                    }

                    if ($listener instanceof ListenerInterface) {
                        if ($listener instanceof ContainerAwareInterface) {
                            $listener->setContainer($this->container);
                        }

                        $listenerAcceptor->addListener($event, $listener);
                        $logger->info('Loaded ' . get_class($listener));
                    }
                }
            }    
        }
    }
}
