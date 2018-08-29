<?php
namespace ValuePad\Core\Appraisal\Exceptions;

use Ascope\Libraries\Validation\PresentableException;

class ReaderNotRelatedException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('The provided user is not related to one (or more) of the provided messages.');
	}
}
