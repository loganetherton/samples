<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Support\Validation\AbstractConstraints;

class Constraints extends AbstractConstraints
{
	const IS_PAID_EQUALS_TRUE = 'isPaidEqualsTrue';
	const PAID_AT_NOT_SPECIFIED = 'paidAtNotSpecified';

	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function isPaidEqualsTrue(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		return !isset($errors['isPaid']) && $source->getValue('isPaid') === true;
	}


	/**
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	public function paidAtNotSpecified(
		SourceHandlerInterface $source,
		ErrorsThrowableCollection $errors
	)
	{
		return !isset($errors['paidAt']) && !$source->hasProperty('paidAt');
	}
}
