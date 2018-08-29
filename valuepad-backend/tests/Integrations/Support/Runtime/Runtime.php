<?php
namespace ValuePad\Tests\Integrations\Support\Runtime;

use ValuePad\Tests\Integrations\Support\Auth\SessionManager;
use Illuminate\Config\Repository as Config;

/**
 * The class represent a run-time within which a specific integration test is executed.
 *
 *
 */
class Runtime
{
    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var Capture
     */
    private $capture;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var Helper
	 */
	private $helper;

    /**
     * @param Capture $capture
     */
    public function setCapture(Capture $capture)
    {
        $this->capture = $capture;
    }

    /**
     * @return Capture
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * @param SessionManager $sessionManager
     */
    public function setSessionManager(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param string $name
     * @return Session
     */
    public function getSession($name)
    {
        return new Session($this->sessionManager->get($name)->getData());
    }

	/**
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * @param Config $config
	 */
	public function setConfig(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * @return Helper
	 */
	public function getHelper()
	{
		return $this->helper;
	}

	/**
	 * @param Helper $helper
	 */
	public function setHelper(Helper $helper)
	{
		$this->helper = $helper;
	}
}
