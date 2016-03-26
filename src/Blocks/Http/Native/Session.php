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
    public function has($key)
    {
        return array_key_exists((string)$key, $_SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $key = (string)$key;
        return (array_key_exists($key, $_SESSION)) ? $_SESSION[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function replace($key, $value = null, $default = null)
    {
        $this->set((string)$key, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value = null)
    {
        $_SESSION[(string)$key] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $key = (string)$key;
        unset($_SESSION[$key]);
        session_commit();
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function destroy()
//    {
//        foreach ($_SESSION as $key => $value) {
//            unset($_SESSION[$key]);
//        }
//        session_destroy();
//        session_commit();
//    }
}
