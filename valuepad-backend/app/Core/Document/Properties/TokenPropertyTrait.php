<?php
namespace ValuePad\Core\Document\Properties;

trait TokenPropertyTrait
{
	/**
	 * @var string
	 */
	private $token;

	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * @param string $token
	 */
	public function setToken($token)
	{
		$this->token = $token;
	}
}
