<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Log\Enums\Action;

class CreateAdditionalDocumentFactory extends AbstractAdditionalDocumentFactory
{
	/**
	 * @return Action
	 */
	protected function getAction()
	{
		return new Action(Action::CREATE_ADDITIONAL_DOCUMENT);
	}
}
