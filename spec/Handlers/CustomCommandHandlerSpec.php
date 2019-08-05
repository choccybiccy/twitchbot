<?php

namespace spec\Choccybiccy\TwitchBot\Handlers;

use Choccybiccy\TwitchBot\Twitch\Client;
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

    public function let(Client $client, LoggerInterface $logger)
    {
        $this->tempCommandsJson = sys_get_temp_dir() . '/' . uniqid('CustomCommandHandlerSpec') . '.json';
        $this->beConstructedWith($client, $this->tempCommandsJson);
        $this->setLogger($logger);
    }

    public function letGo()
    {
        if (file_exists($this->tempCommandsJson)) {
            @unlink($this->tempCommandsJson);
        }
    }

    public function it_should_set_commands()
    {
        $commands = [
            'someCommand' => uniqid('someCommand'),
            'someOtherCommand' => uniqid('someOtherCommand'),
        ];
        $this->setCommands($commands);
        $this->getCommands()->shouldReturn($commands);
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

    public function it_should_addcommand(Client $client, WebSocket $socket)
    {
        $user = ['login' => uniqid('broadcaster_')];
        $moderators = [
            ['user_id' => mt_rand(10000, 99999), 'user_name' => uniqid('moderator1_')],
            ['user_id' => mt_rand(10000, 99999), 'user_name' => uniqid('moderator2_')],
        ];
        $client->getUser()->willReturn($user);
        $this->handle(
            ":{$user['login']}!user@channel.tmi.twitch.tv PRIVMSG #channel :!addcommand key some value",
            $socket
        );
        $this->getCommands()->shouldHaveKeyWithValue('key', 'some value');
    }

    public function it_should_removecommand(Client $client, WebSocket $socket)
    {
        $user = ['login' => uniqid('broadcaster_')];
        $moderators = [
            ['user_id' => mt_rand(10000, 99999), 'user_name' => uniqid('moderator1_')],
            ['user_id' => mt_rand(10000, 99999), 'user_name' => uniqid('moderator2_')],
        ];
        $client->getUser()->willReturn($user);
        $client->getModerators()->willReturn(new Collection($moderators));
        $this->addCommand('key', 'some value');
        $this->handle(":{$user['login']}!user@channel.tmi.twitch.tv PRIVMSG #channel :!removecommand key", $socket);
        $this->getCommands()->shouldNotHaveKeyWithValue('key', 'some value');
    }

    public function it_should_run_custom_command(WebSocket $socket)
    {
        $this->setCommands(['somecommand' => 'some output']);
        $socket->send('PRIVMSG #channel :some output')->shouldBeCalled();
        $this->handle(":user!user@channel.tmi.twitch.tv PRIVMSG #channel :!somecommand", $socket);
    }
}
