<?php
namespace ValuePad\Core\User\Exceptions;

use Ascope\Libraries\Validation\PresentableException;

class EmailNotFoundException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('Unable to find email for the provided user');
	}
}
