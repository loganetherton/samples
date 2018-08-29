<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use ValuePad\Core\Appraisal\Persistables\MessagePersistable;

class MessagesProcessor extends AbstractMessagesProcessor
{
	/**
	 * @return MessagePersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new MessagePersistable());
	}
}
