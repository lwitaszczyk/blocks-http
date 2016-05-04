<?php

namespace Blocks\Http\Request;

use Blocks\Http\Request;

class RequestFromGlobals extends Request
{

    /**
     * @return bool
     */
    public function isAjax()
    {
        return (strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return filter_input(INPUT_SERVER, 'HTTP_HOST');
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_SCHEME');
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_URI');
    }

    /**
     * @return string
     */
    public function getScriptName()
    {
        return trim(filter_input(INPUT_SERVER, 'SCRIPT_NAME'), '/');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $path = filter_input(INPUT_SERVER, 'PATH_INFO');
        return (is_null($path)) ? '/' : $path;
    }

    /**
     * @param $type
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed|null
     */
    private function getValueFromArray($type, $name = null, $default = null)
    {
        if (null === $name) {
            return filter_input_array($type);
        } else {
            $value = filter_input($type, $name);

            return (null !== $value) ? $value : $default;
        }
    }

    /**
     * @param null $name
     * @param array $default
     * @return mixed|null
     */
    public function getPost($name = null, $default = [])
    {
        return $this->getValueFromArray(INPUT_POST, $name, $default);
    }

    /**
     * @deprecated
     * @param null $name
     * @param array $default
     * @return mixed|null
     */
    public function getGet($name = null, $default = [])
    {
        return $this->getValueFromArray(INPUT_GET, $name, $default);
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->getValueFromArray(INPUT_GET);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryValue($key, $default = null)
    {
        $value = $this->getQueryParams();

        $keys = explode('.', $key);
        $key = reset($keys);

        do {
            if (array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return $default;
            }
            $key = next($keys);
        } while ($key !== false);

        return $value;
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->getValueFromArray(INPUT_COOKIE);
    }

    /**
     * @return mixed
     */
    public function getClientIP()
    {
        $httpHeaders = [
            'HTTP_TRUE_CLIENT_IP',
            'X_FORWARDED_FOR',
            'HTTP_X_FORWARDED_FOR',
            'CLIENT_IP',
            'REMOTE_ADDR',
        ];
        foreach ($httpHeaders as $key) {
            $ip = filter_input(INPUT_SERVER, $key);
            if (!is_null($ip)) {
                return $ip;
            }
        }

        return filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    }

    /**
     * @return string|null
     */
    public function getUserAgent()
    {
        return filter_input(INPUT_REQUEST, 'HTTP_USER_AGENT');
    }

    /**
     * @return string
     */
    public function getClientLocale()
    {
        return filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
    }

    /**
     * @return string
     */
    public function getRawContent()
    {
        return file_get_contents('php://input');
    }
}
