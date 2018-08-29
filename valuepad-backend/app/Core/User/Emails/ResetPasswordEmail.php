<?php
namespace ValuePad\Core\User\Emails;

use ValuePad\Core\Support\Letter\Email;
use ValuePad\Core\User\Entities\Token;

class ResetPasswordEmail extends Email
{
	/**
	 * @var Token
	 */
	private $token;

	/**
	 * @param Token $token
	 */
	public function __construct(Token $token)
	{
		$this->token = $token;
	}

	/**
	 * @return Token
	 */
	public function getToken()
	{
		return $this->token;
	}
}
