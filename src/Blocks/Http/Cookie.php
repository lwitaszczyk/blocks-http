<?php

namespace Blocks\Http;

interface Cookie
{

    /**
     * @param $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function get(
        $name,
        $default = null
    );

    /**
     * @param string $name
     * @param mixed  $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httponly
     *
     * @return self
     */
    public function set(
        $name,
        $value = null,
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httponly = false
    );

    /**
     * @param string $name
     *
     * @return self
     */
    public function delete($name);
}
