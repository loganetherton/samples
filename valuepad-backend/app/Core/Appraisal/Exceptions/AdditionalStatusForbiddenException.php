<?php
namespace ValuePad\Core\Appraisal\Exceptions;

use Ascope\Libraries\Validation\PresentableException;

class AdditionalStatusForbiddenException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('The provided additional status does not belong to the provided customer.');
	}
}
