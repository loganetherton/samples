<?php
namespace ValuePad\DAL\Session\Support;

use ValuePad\Core\Session\Interfaces\SessionPreferenceInterface;
use Illuminate\Contracts\Config\Repository as Config;

/**
 *
 * @author Sergei Melnikov <me@rnr.name>
 */
class SessionPreference implements SessionPreferenceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        return $this->config->get('session.lifetime', 60);
    }

	/**
	 * @return int
	 */
	public function getAutoLoginTokenLifetime()
	{
		return $this->config->get('session.auto_login_token_lifetime', 5);
	}
}