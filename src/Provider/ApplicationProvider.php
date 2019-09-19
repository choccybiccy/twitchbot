<?php

namespace Choccybiccy\TwitchBot\Provider;

use Choccybiccy\TwitchBot\Application;
use Choccybiccy\TwitchBot\Twitch\Client;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Event\Emitter;
use League\Flysystem\Adapter\Local;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\Config;
use Psr\Log\LoggerInterface;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;

/**
 * Class ApplicationProvider.
 */
class ApplicationProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'react.client',
        LoggerInterface::class,
        Application::class,
    ];

    /**
     * @var Config
     */
    protected $config;

    /**
     * ApplicationProvider construct.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->container->add('react.loop', function () {
            return Factory::create();
        }, true);
        $this->container->add('react.client.twitch', function () {
            $loop = $this->container->get('react.loop');
            $reactConnector = new \React\Socket\Connector($loop, [
                'dns' => '8.8.8.8',
                'timeout' => 10
            ]);
            $connector = new Connector($loop, $reactConnector);
            return $connector('wss://irc-ws.chat.twitch.tv:443');
        });

        $this->container->add(FilesystemInterface::class, function () {
            $adapter = new Local(__DIR__ . '/../../var');
            return new Filesystem($adapter);
        });

        $this->container->add(LoggerInterface::class, function () {
            return new Logger('twitchbot', [
                new StreamHandler('php://stderr', $this->config->get('application.log.level') ?? Logger::DEBUG)
            ]);
        });

        $this->container->add(Client::class, new Client(
            $this->config->get('broadcaster.token')
        ));

        $this->container->add(Emitter::class, function () {
            $emitter = new Emitter();
            $emitter->useListenerProvider(new EventProvider(
                $this->container, 
                $this->config->get('events')
            ));
            return $emitter;
        });
        
        $this->container->add(Application::class, function () {
            return new Application(
                $this->config->get('bot.nickname'),
                $this->config->get('bot.channels'),
                $this->config->get('bot.token'),
                $this->container->get('react.loop'),
                $this->container->get('react.client.twitch'),
                $this->container->get(Emitter::class),
                $this->container->get(LoggerInterface::class)
            );
        });
    }
}
