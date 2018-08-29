<?php
namespace ValuePad\Core\User\Interfaces;

interface EmailHolderInterface
{
	/**
	 * @return string
	 */
	public function getEmail();

	/**
	 * @param string $email
	 */
	public function setEmail($email);
}
