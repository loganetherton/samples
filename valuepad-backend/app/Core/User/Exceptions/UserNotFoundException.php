<?php
namespace ValuePad\Core\User\Exceptions;

use Ascope\Libraries\Validation\PresentableException;

class UserNotFoundException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('The user has not been found with the provided details');
	}
}
