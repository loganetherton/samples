<?php
namespace ValuePad\Core\User\Properties\Device;

trait TokenPropertyTrait
{
	/**
	 * @var string
	 */
	private $token;

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
}
