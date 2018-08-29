<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Log\Enums\Action;

class DeleteAdditionalDocumentFactory extends AbstractAdditionalDocumentFactory
{
	/**
	 * @return Action
	 */
	protected function getAction()
	{
		return new Action(Action::DELETE_ADDITIONAL_DOCUMENT);
	}
}
