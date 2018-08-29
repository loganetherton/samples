<?php
namespace ValuePad\Core\User\Exceptions;

use Ascope\Libraries\Validation\PresentableException;

class InvalidTokenException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('The token is invalid.');
	}
}
