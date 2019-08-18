<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Twitch\Client;
use League\Flysystem\FilesystemInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Ratchet\Client\WebSocket;
use Tightenco\Collect\Support\Collection;

/**
 * Class CustomCommandHandlerSpec.
 */
class CustomCommandHandlerSpec extends ObjectBehavior
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
        $output = uniqid('someCommand');
        $this->addCommand('someCommand', $output);
        $this->getCommands()->shouldReturn(['someCommand' => $output]);
    }

    public function it_should_handle_addcommand()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!addcommand key value')
            ->shouldReturn(true);
    }

    public function it_should_handle_removecommand()
    {
        $this->canHandle(':user!user@channel.tmi.twitch.tv PRIVMSG #channel :!removecommand key')
            ->shouldReturn(true);
    }

    public function it_should_addcommand(Client $client, FilesystemInterface $filesystem, WebSocket $socket)
    {
        $user = ['login' => uniqid('broadcaster_')];
        $client->getUser()->willReturn($user);

        $filesystem->put('commands.json', json_encode(['key' => 'some value']))->shouldBeCalled();
        $this->handle(
            ":{$user['login']}!user@channel.tmi.twitch.tv PRIVMSG #channel :!addcommand key some value",
            $socket
        );
        $this->getCommands()->shouldHaveKeyWithValue('key', 'some value');
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

        $this->addCommand('key', 'some value');

        $filesystem->put('commands.json', json_encode([]))->shouldBeCalled();
        $this->handle(":{$user['login']}!user@channel.tmi.twitch.tv PRIVMSG #channel :!removecommand key", $socket);
        $this->getCommands()->shouldNotHaveKeyWithValue('key', 'some value');
    }

    public function it_should_run_custom_command(WebSocket $socket)
    {
        $this->addCommand('somecommand', 'some output');
        $socket->send('PRIVMSG #channel :some output')->shouldBeCalled();
        $this->handle(":user!user@channel.tmi.twitch.tv PRIVMSG #channel :!somecommand", $socket);
    }
}
