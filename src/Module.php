<?php

namespace Choccybiccy\TwitchBot;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;

/**
 * Abstract class Module.
 */
abstract class Module implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Load the module.
     *
     * @return void
     */
    abstract protected function load();
}
