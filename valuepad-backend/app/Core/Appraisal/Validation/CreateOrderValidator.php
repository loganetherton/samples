<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\Binder;

class CreateOrderValidator extends AbstractOrderValidator
{
	use AdditionalDocumentValidatorTrait;

	/**
	 * @param Binder $binder
	 */
	public function define(Binder $binder)
	{
		parent::define($binder);

		$this->defineAdditionalDocument($binder, $this->container, $this->customer,
			['namespace' => 'contractDocument']);
	}
}
