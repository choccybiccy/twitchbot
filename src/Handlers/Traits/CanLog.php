<?php

namespace Choccybiccy\TwitchBot\Handlers\Traits;

use Psr\Log\LoggerInterface;

/**
 * Trait CanLog.
 */
trait CanLog
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}