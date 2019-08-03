<?php

namespace Choccybiccy\TwitchBot\Providers;

use Choccybiccy\TwitchBot\Application;
use Choccybiccy\TwitchBot\Handlers\EchoHandler;
use Choccybiccy\TwitchBot\Handlers\KeepAliveHandler;
use Depotwarehouse\OAuth2\Client\Twitch\Provider\Twitch;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\OAuth2\Client\Provider\GenericProvider;
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
        Twitch::class,
        Application::class,
    ];

    /**
     * @return void
     */
    public function register()
    {
        $this->getContainer()->add('react.client.twitch', function () {
            return connect('wss://irc-ws.chat.twitch.tv:443');
        });

        $this->getContainer()->add(LoggerInterface::class, function () {
            return new Logger('twitchbot', [
                new StreamHandler('php://stderr', getenv('TWITCHBOT_LOG_LEVEL') ?? Logger::DEBUG)
            ]);
        });

        $this->getContainer()->add(Application::class, function () {
            return new Application(
                getenv('TWITCHBOT_NICKNAME'),
                getenv('TWITCHBOT_CHANNEL'),
                getenv('TWITCHBOT_OAUTH_TOKEN'),
                $this->container->get('react.client.twitch'),
                [new KeepAliveHandler],
                $this->container->get(LoggerInterface::class)
            );
        });
    }
}
