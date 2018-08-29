<?php
namespace ValuePad\Core\User\Properties;

use ValuePad\Core\User\Entities\User;

trait UserPropertyTrait
{
	/**
	 * @var User
	 */
	private $user;

	/**
	 * @param User $user
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
