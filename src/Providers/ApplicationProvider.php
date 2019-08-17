<?php

namespace Choccybiccy\TwitchBot\Providers;

use Choccybiccy\TwitchBot\Application;
use Choccybiccy\TwitchBot\Handlers\AnnouncementHandler;
use Choccybiccy\TwitchBot\Handlers\CountdownCommandHandler;
use Choccybiccy\TwitchBot\Handlers\CustomCommandHandler;
use Choccybiccy\TwitchBot\Handlers\KeepAliveHandler;
use Choccybiccy\TwitchBot\Handlers\ListCommandsHandler;
use Choccybiccy\TwitchBot\Handlers\QueueHandler;
use Choccybiccy\TwitchBot\Handlers\TwitchStatsHandler;
use Choccybiccy\TwitchBot\Twitch\Client;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function Ratchet\Client\connect;
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
                new StreamHandler('php://stderr', getenv('TWITCHBOT_LOG_LEVEL') ?? Logger::DEBUG)
            ]);
        });

        $this->container->add(Client::class, new Client(getenv('TWITCHBOT_BROADCASTER_TOKEN')));

        $this->loadHandlers();
        $this->container->add(Application::class, function () {
            return new Application(
                getenv('TWITCHBOT_BOT_NICKNAME'),
                getenv('TWITCHBOT_CHANNEL'),
                getenv('TWITCHBOT_BOT_TOKEN'),
                $this->container->get('react.loop'),
                $this->container->get('react.client.twitch'),
                $this->container->get(FilesystemInterface::class),
                [
                    $this->container->get(KeepAliveHandler::class),
                    $this->container->get(TwitchStatsHandler::class),
                    $this->container->get(CustomCommandHandler::class),
                    $this->container->get(CountdownCommandHandler::class),
                    $this->container->get(QueueHandler::class),
                    $this->container->get(AnnouncementHandler::class),
                    $this->container->get(ListCommandsHandler::class),
                ],
                $this->container->get(LoggerInterface::class)
            );
        });
    }

    /**
     * Load all the twitchbot handlers.
     */
    protected function loadHandlers(): void
    {
        $this->container->add(KeepAliveHandler::class, new KeepAliveHandler());
        $this->container->add(TwitchStatsHandler::class, function () {
            return new TwitchStatsHandler(
                getenv('TWITCHBOT_BROADCASTER_NICKNAME'),
                $this->container->get(Client::class)
            );
        });
        $this->container->add(CustomCommandHandler::class, function () {
            return new CustomCommandHandler($this->container->get(Client::class));
        });
        $this->container->add(CountdownCommandHandler::class, function () {
            return new CountdownCommandHandler($this->container->get(Client::class));
        });
        $this->container->add(QueueHandler::class, function () {
            return new QueueHandler($this->container->get(Client::class));
        });
        $this->container->add(AnnouncementHandler::class, function () {
            return new AnnouncementHandler($this->container->get(Client::class));
        });
        $this->container->add(ListCommandsHandler::class, function () {
            return new ListCommandsHandler([
                $this->container->get(TwitchStatsHandler::class),
                $this->container->get(CountdownCommandHandler::class),
                $this->container->get(CustomCommandHandler::class),
                $this->container->get(QueueHandler::class),
            ]);
        });
    }
}
