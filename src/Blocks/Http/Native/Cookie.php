<?php

namespace Blocks\Http\Native;

use Blocks\Http\Cookie as CookieInterface;

class Cookie implements CookieInterface
{

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        $name = (string)$name;
        $value = filter_input(INPUT_COOKIE, $name);
        return ($value != null) ? $value : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(
        $name,
        $value = null,
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httponly = false
    ) {
        setcookie(
            (string)$name,
            $value,
            (int)$expire,
            $path,
            $domain,
            $secure,
            $httponly
        );
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($name)
    {
        $name = (string)$name;
        unset($_COOKIE[$name]);
        setcookie($name, null, -1);
    }
}
