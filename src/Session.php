<?php

namespace Oktave;

use Oktave\Interfaces\Storage;

/**
 *  Session is the PHP session storage mechanism that will store authentication keys
 */
class Session implements Storage
{
    const SESSION_KEY = 'oktaveSDK';

    public function __construct()
    {
        session_id() || session_start();
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        return $this;
    }

    public function getKey(string $key, $default = null)
    {
        return $_SESSION[self::SESSION_KEY][$key] ?? $default;
    }

    public function setKey(string $key, $value)
    {
        $_SESSION[self::SESSION_KEY][$key] = $value;
        return $this;
    }

    public function removeKey(string $key)
    {
        unset($_SESSION[self::SESSION_KEY][$key]);
        return $this;
    }

}
