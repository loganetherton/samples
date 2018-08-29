<?php
namespace ValuePad\Core\Appraisal\Exceptions;

use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\PresentableException;

class ValidationAggregateException extends PresentableException
{
	/**
	 * @var ErrorsThrowableCollection
	 */
	private $orderErrors;

	/**
	 * @var ErrorsThrowableCollection
	 */
	private $invitationErrors;

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return $this->invitationErrors === null && $this->orderErrors === null;
	}

	/**
	 * @param ErrorsThrowableCollection $errors
	 */
	public function setOrderErrors(ErrorsThrowableCollection $errors)
	{
		$this->orderErrors = $errors;
	}

	/**
	 * @return ErrorsThrowableCollection
	 */
	public function getOrderErrors()
	{
		return $this->orderErrors;
	}

	/**
	 * @return ErrorsThrowableCollection
	 */
	public function getInvitationErrors()
	{
		return $this->invitationErrors;
	}

	/**
	 * @param ErrorsThrowableCollection $errors
	 */
	public function setInvitationErrors(ErrorsThrowableCollection $errors)
	{
		$this->invitationErrors = $errors;
	}
}
