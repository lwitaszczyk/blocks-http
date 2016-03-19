<?php

namespace Blocks\Http;

interface Session
{
    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists($key);

    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null);

    /**
     * @param string     $key
     * @param mixed|null $value
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function replace($key, $value = null, $default = null);

    /**
     * @param string     $key
     * @param mixed|null $value
     *
     * @return self
     */
    public function set($key, $value = null);

    /**
     * @param string $key
     *
     * @return self
     */
    public function delete($key);

    /**
     * @return self
     */
    public function destroy();
}
