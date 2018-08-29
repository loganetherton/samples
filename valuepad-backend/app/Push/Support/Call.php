<?php
namespace ValuePad\Push\Support;

use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;

class Call
{
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $secret1;

	/**
	 * @var string
	 */
	private $secret2;

	/**
	 * @var User|Amc|Customer
	 */
	private $user;

	/**
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $secret
	 */
	public function setSecret1($secret)
	{
		$this->secret1 = $secret;
	}

	/**
	 * @return string
	 */
	public function getSecret1()
	{
		return $this->secret1;
	}

	/**
	 * @param string $secret
	 */
	public function setSecret2($secret)
	{
		$this->secret2 = $secret;
	}

	/**
	 * @return string
	 */
	public function getSecret2()
	{
		return $this->secret2;
	}

	/**
	 * @param User|Amc|Customer $user
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return User|Amc|Customer
	 */
	public function getUser()
	{
		return $this->user;
	}
}
