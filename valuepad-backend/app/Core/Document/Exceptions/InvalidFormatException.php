<?php
namespace ValuePad\Core\Document\Exceptions;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Document\Enums\Format;

class InvalidFormatException extends PresentableException
{
	public function __construct()
	{
		parent::__construct('The document must be in one of the following formats: '.implode(', ', Format::toArray()));
	}
}
