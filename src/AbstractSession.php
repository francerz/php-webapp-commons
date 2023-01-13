<?php

namespace Francerz\WebappCommons;

use RuntimeException;

abstract class AbstractSession
{
    /**
     * @throws RuntimeException
     */
    public static function start()
    {
        switch (session_status()) {
            case PHP_SESSION_NONE:
                if (headers_sent()) {
                    throw new RuntimeException('Session start failed! Headers already sent.');
                }
                session_start();
                break;
            case PHP_SESSION_DISABLED:
                throw new RuntimeException('Sessions start failed! Sessions disabled.');
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value)
    {
        static::start();
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        static::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function unset(string $key)
    {
        $val = static::get($key);
        unset($_SESSION[$key]);
        return $val;
    }

    public static function destroy()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        session_destroy();
    }
}
