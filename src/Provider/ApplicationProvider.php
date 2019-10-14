<?php

namespace Choccybiccy\TwitchBot\Provider;

use Choccybiccy\TwitchBot\Application;
use Choccybiccy\TwitchBot\EventListener\DebugListener;
use Choccybiccy\TwitchBot\Twitch\Client;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\Container;
use League\Event\Emitter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\Config;
use Psr\Log\LoggerInterface;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

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
     * @var Container
     */
    protected $container;

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
        $this->container->add(LoopInterface::class, function () {
            return Factory::create();
        }, true);

        $this->container->add(LoggerInterface::class, function () {
            return new Logger('twitchbot', [
                new StreamHandler(
                    'php://stderr',
                    $this->config->get('application.log.level') ?? Logger::DEBUG
                )
            ]);
        });

        $this->container->add(Client::class, new Client(
            $this->config->get('broadcaster.token')
        ));

        $this->container->add(DebugListener::class, function () {
            return new DebugListener(
                $this->container->get(LoggerInterface::class)
            );
        });
        $this->container->add(Emitter::class, function () {
            $emitter = new Emitter();
            $emitter->useListenerProvider(new EventProvider(
                $this->container, 
                $this->config->get('events')
            ));
            return $emitter;
        });
        
        $this->container->add(Application::class, function () {

            $loop = $this->container->get(LoopInterface::class);
            $reactConnector = new \React\Socket\Connector($loop, [
                'dns' => $this->config->get('twitch.connection.dns', '8.8.8.8'),
                'timeout' => $this->config->get(
                    'twitch.connection.timeout',
                    10
                )
            ]);
            $connector = new Connector($loop, $reactConnector);

            return new Application(
                $this->config->get('bot.nickname', 'choccyb0t'),
                $this->config->get('bot.channels'),
                $this->config->get('bot.token'),
                $this->container->get(LoopInterface::class),
                $connector(
                    $this->config->get(
                        'twitch.url',
                        'wss://irc-ws.chat.twitch.tv:443'
                    )
                ),
                $this->container->get(Emitter::class),
                $this->container->get(LoggerInterface::class)
            );
        });

        $this->loadModules();
    }

    /**
     * Load modules.
     *
     * @return void
     */
    protected function loadModules()
    {
        $logger = $this->container->get(LoggerInterface::class);
        $modules = $this->config->get('modules');
        if (is_array($modules)) {
            foreach ($modules as $module) {
                try {
                    if (class_exists($module)) {
                        $module = (new $module)->setContainer($this->container);
                        $module->load();
                    } else {
                        throw new \Exception('Class not found');
                    }
                } catch (\Exception $e) {
                    $logger->error(sprintf(
                        'Unable to load module %s: %s',
                        $module,
                        $e->getMessage()
                    ));
                }
            }
        }
    }
}
