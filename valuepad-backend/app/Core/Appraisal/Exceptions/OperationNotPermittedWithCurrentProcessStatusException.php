<?php
namespace ValuePad\Core\Appraisal\Exceptions;

use Ascope\Libraries\Validation\PresentableException;

class OperationNotPermittedWithCurrentProcessStatusException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('Operation is not permitted with the current process status.');
	}
}
