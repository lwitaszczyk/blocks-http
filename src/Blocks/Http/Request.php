<?php

namespace Blocks\Http;

abstract class Request
{

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_HEAD = 'HEAD';
    const METHOD_TRACE = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';

    /**
     * @var string[]
     */
    private $attributes;

    /**
     * @var mixed[]
     */
    private $parameters;

    /**
     * @var string
     */
    private $currentPath;

    /**
     *
     */
    public function __construct()
    {
        $this->attributes = [];
        $this->parameters = [];
        $this->currentPath = '/' . trim($this->getPathInfo(), '/');

        $this->setAttribute('ajax', $this->isAjax());
        $this->setAttribute('host', $this->getHost());
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[(string)$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[(string)$name] = $value;
        return $this;
    }

    /**
     * @return \mixed[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    public function getParameter($name, $default = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    /**
     * @return bool
     */
    abstract public function isAjax();

    /**
     * @return string
     */
    abstract public function getHost();

    /**
     * @return string
     */
    abstract public function getMethod();

    /**
     * @return string
     */
    abstract public function getUri();

    /**
     * @return string
     */
    abstract public function getScriptName();

    /**
     * @return string
     */
    abstract public function getPathInfo();

    /**
     * @param null $name
     * @param array $default
     * @return mixed|null
     */
    abstract public function getPost($name = null, $default = []);

    /**
     * @param null $name
     * @param array $default
     * @return mixed|null
     */
    abstract public function getGet($name = null, $default = []);

    /**
     * @return mixed
     */
    abstract public function getClientIP();

    /**
     * @return string|null
     */
    abstract public function getUserAgent();

    /**
     * @return string
     */
    abstract public function getClientLocale();

    /**
     * @deprecated
     * @return string
     */
    public function getCurrentPath()
    {
        return $this->currentPath;
    }

    /**
     * @deprecated
     * @param string $currentPath
     * @return $this
     */
    public function setCurrentPath($currentPath)
    {
        $this->currentPath = '/' . trim($currentPath, '/');
        return $this;
    }

    /**
     * @return string
     */
    abstract public function getRawContent();
}
