<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use ValuePad\Api\Appraisal\V2_0\Processors\AbstractMessagesProcessor;
use ValuePad\Core\Customer\Persistables\MessagePersistable;

class MessagesProcessor extends AbstractMessagesProcessor
{
	protected function configuration()
	{
		$configuration = parent::configuration();

		$configuration['employee'] = 'string';

		return $configuration;
	}

	/**
	 * @return MessagePersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new MessagePersistable());
	}
}
