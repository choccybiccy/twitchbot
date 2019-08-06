<?php

namespace Choccybiccy\TwitchBot\Providers;

use Choccybiccy\TwitchBot\Application;
use Choccybiccy\TwitchBot\Handlers\CountdownCommandHandler;
use Choccybiccy\TwitchBot\Handlers\CustomCommandHandler;
use Choccybiccy\TwitchBot\Handlers\KeepAliveHandler;
use Choccybiccy\TwitchBot\Handlers\TwitchStatsHandler;
use Choccybiccy\TwitchBot\Twitch\Client;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use function Ratchet\Client\connect;

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
        $this->container->add('react.client.twitch', function () {
            return connect('wss://irc-ws.chat.twitch.tv:443');
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
                $this->container->get('react.client.twitch'),
                [
                    $this->container->get(KeepAliveHandler::class),
                    $this->container->get(TwitchStatsHandler::class),
                    $this->container->get(CustomCommandHandler::class),
                    $this->container->get(CountdownCommandHandler::class),
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
    }
}
