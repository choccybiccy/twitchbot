<?php

namespace Choccybiccy\TwitchBot\Handlers\Traits;

/**
 * Trait CanReadChat.
 */
trait CanReadChat
{
    /**
     * @param string $message
     *
     * @return string
     */
    public function getUser(string $message): string
    {
        return $this->extractMessageParts($message)['user'];
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function getChannel(string $message): ?string
    {
        return $this->extractMessageParts($message)['channel'];
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function getMessage(string $message): ?string
    {
        return trim($this->extractMessageParts($message)['message']);
    }

    /**
     * @param string $message
     *
     * @return array
     */
    protected function extractMessageParts(string $message): array
    {
        $return = ['user' => null, 'channel' => null, 'message' => null];
        if (preg_match('/^\:(.*)?\!.*PRIVMSG #(.*)? :(.*)/', $message, $matches)) {
            $return['user'] = $matches[1];
            $return['channel'] = $matches[2];
            $return['message'] = $matches[3];
        }
        return $return;
    }
}
