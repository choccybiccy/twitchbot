<?php

namespace Choccybiccy\TwitchBot;

use Noodlehaus\Config as BaseConfig;

/**
 * Class Config.
 */
class Config extends BaseConfig
{
    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $value = parent::get($key, $default);
        if (is_string($value)
            && preg_match('/^\%env\(([A-Z0-9_]+)\)\%$/', $value, $matches)
        ) {
            $value = getenv($matches[1]) ?: $default;
        }
        return $value;
    }
}