<?php
namespace ValuePad\Core\User\Interfaces;

interface PasswordPreferenceInterface
{
	/**
	 * @return int
	 */
	public function getResetTokenLifetime();
}
