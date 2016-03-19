<?php

namespace Blocks\Http\Native;

use Blocks\Http\Session as SessionInterface;

class Session implements SessionInterface
{

    /**
     * {@inheritdoc}
     */
    public function __construct($sid = null, $expire = 60)
    {
        $sid = (string)$sid;
        if (!is_null($sid)) {
            session_name($sid);
        }
        session_cache_expire($expire);
        session_start();
        session_regenerate_id();
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        $key = (string)$key;

        return (isset($_SESSION[$key]));
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $key = (string)$key;

        return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function replace($key, $value = null, $default = null)
    {
        $key = (string)$key;
        $r = $this->get($key, $default);
        $this->set($key, $value);

        return $r;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value = null)
    {
        $key = (string)$key;
        $_SESSION[$key] = $value;

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $key = (string)$key;
        unset($_SESSION[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        foreach ($_SESSION as $key => $value) {
            unset($_SESSION[$key]);
        }
        session_destroy();
        session_commit();
    }
}
