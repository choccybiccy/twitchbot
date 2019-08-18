<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Twitch\Client;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;
use Tightenco\Collect\Support\Collection;

/**
 * Class CountdownCommandHandlerSpec.
 */
class CountdownCommandHandlerSpec extends ObjectBehavior
{
    /**
     * @var string
     */
    protected $tempCommandsJson;

    public function let(Client $client, LoggerInterface $logger, FilesystemInterface $filesystem)
    {
        $this->beConstructedWith($client);
        $this->setLogger($logger);
        $this->setFilesystem($filesystem);
    }

    public function it_should_add_commands()
    {
        $output = (new \DateTime('tomorrow'))->format('Y-m-d H:i');
        $this->addCommand('someCommand', $output);
        $this->getCommands()->shouldReturn(['someCommand' => $output]);
    }

    public function it_should_handle_addcommand()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!addcountdown key value')
            ->shouldReturn(true);
    }

    public function it_should_handle_removecommand()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!removecountdown key')
            ->shouldReturn(true);
    }

    public function it_should_addcommand(Client $client, FilesystemInterface $filesystem, WebSocket $socket)
    {
        $user = ['login' => uniqid('broadcaster_')];
        $client->getUser()->willReturn($user);
        $date = (new \DateTime('tomorrow'))->format('Y-m-d H:i');

        $filesystem->put('countdowns.json', json_encode(['key' => $date]))->shouldBeCalled();
        $this->handle(
            ":{$user['login']}!user@channel.tmi.twitch.tv PRIVMSG #channel :!addcountdown key " . $date,
            $socket
        );
        $this->getCommands()->shouldHaveKeyWithValue('key', $date);
    }

    public function it_should_removecommand(Client $client, FilesystemInterface $filesystem, WebSocket $socket)
    {
        $user = ['login' => uniqid('broadcaster_')];
        $moderators = [
            ['user_id' => mt_rand(10000, 99999), 'user_name' => uniqid('moderator1_')],
            ['user_id' => mt_rand(10000, 99999), 'user_name' => uniqid('moderator2_')],
        ];
        $client->getUser()->willReturn($user);
        $client->getModerators()->willReturn(new Collection($moderators));
        $date = (new \DateTime('tomorrow'))->format('Y-m-d H:i');
        $this->addCommand('key', $date);

        $filesystem->put('countdowns.json', json_encode([]))->shouldBeCalled();
        $this->handle(":{$user['login']}!user@channel.tmi.twitch.tv PRIVMSG #channel :!removecountdown key", $socket);
        $this->getCommands()->shouldNotHaveKeyWithValue('key', $date);
    }
}
