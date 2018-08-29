<?php
namespace ValuePad\Tests\Integrations\Support\Auth;

class Session
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $isInitialized = false;

    /**
     * Session constructor.
     * @param $name
     * @param array|null $config
     */
    public function __construct($name, array $config = null)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getConfig($name = null)
    {
        if ($name){
            return $this->config[$name];
        }

        return $this->config;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    public function clearToken()
    {
        $this->token = null;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param bool $flag
     */
    public function setInitialized($flag)
    {
        $this->isInitialized = $flag;
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }


    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->config === null;
    }
}
