<?php
namespace ValuePad\Core\Appraiser\Exceptions;

use Ascope\Libraries\Validation\PresentableException;

class LicenseNotAllowedException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('The provided license does not belong to the provided appraiser.');
	}
}
