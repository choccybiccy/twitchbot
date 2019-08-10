<?php

namespace spec\Choccybiccy\TwitchBot\Handlers\Traits;

/**
 * Class MessageMaker
 */
trait MessageMaker
{
    /**
     * @param string $message
     * @param string|null $user
     * @param string|null $channel
     *
     * @return string
     */
    public function makeInboundMessage($message, string $user = 'user', string $channel = 'channel'): string
    {
        return sprintf(':%s!%s@channel.tmi.twitch.tv PRIVMSG #%s :%s', $user, $user, $channel, $message);
    }

    /**
     * @param string $message
     * @param string $channel
     *
     * @return string
     */
    public function makeOuboundMessage(string $message, string $channel = 'channel'): string
    {
        return sprintf('PRIVMSG #%s :%s', $channel, $message);
    }
}