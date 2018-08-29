<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Appraisal\Persistables\ContactPersistable;

class UniqueContactType extends AbstractRule
{
	public function __construct()
	{
		$this->setIdentifier('unique');
		$this->setMessage('Contact types must be unique.');
	}

	/**
	 * @param array $contacts
	 * @return Error|null
	 */
	public function check($contacts)
	{
		$types = [];

		/**
		 * @var ContactPersistable[] $contacts
		 */
		foreach ($contacts as $contact){

			$type = $contact->getType()->value();

			if (in_array($type, $types)){
				return $this->getError();
			}

			$types[] = $type;
		}

		return null;
	}
}
