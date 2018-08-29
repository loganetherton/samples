<?php
namespace ValuePad\Core\Support\Validation;

use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\SourceHandlerInterface;

abstract class AbstractConstraints
{
	/**
	 * @param array $options
	 * @param array $or
	 * @return callable
	 */
	public function are(array $options, array $or = [])
	{
		return function(SourceHandlerInterface $source, ErrorsThrowableCollection $errors) use ($options, $or){
			return $this->processConstraints($options, $source, $errors)
			|| ($or && $this->processConstraints($or, $source, $errors));
		};
	}

	/**
	 * @param array $constraints
	 * @param SourceHandlerInterface $source
	 * @param ErrorsThrowableCollection $errors
	 * @return bool
	 */
	private function processConstraints(
        array $constraints,
        SourceHandlerInterface $source,
        ErrorsThrowableCollection $errors
	)
	{
		$result = true;

		foreach ($constraints as $constraint){
			$result = $result && call_user_func([$this, $constraint], $source, $errors);
		}

		return $result;
	}
}
