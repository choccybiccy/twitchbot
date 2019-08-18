<?php

namespace Choccybiccy\TwitchBot\Handlers;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Class CountdownCommandHandler.
 */
class CountdownCommandHandler extends CustomCommandHandler
{
    /**
     * @var string
     */
    protected $commandsFile = 'countdowns.json';

    /**
     * @var string
     */
    protected $addCommandString = 'addcountdown';

    /**
     * @var string
     */
    protected $removeCommandString = 'removecountdown';

    /**
     * @param string $key
     *
     * @return string
     * @throws \Exception
     */
    protected function getMessageForOutput(string $key): string
    {
        $date = new Carbon($this->commands[$key]);
        return $date->diffForHumans(['parts' => 3, 'join' => true], CarbonInterface::DIFF_ABSOLUTE);
    }
}
