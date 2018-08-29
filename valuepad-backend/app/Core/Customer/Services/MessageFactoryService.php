<?php
namespace ValuePad\Core\Customer\Services;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Services\AbstractMessageFactoryService;
use ValuePad\Core\Customer\Entities\Message;
use ValuePad\Core\Appraisal\Entities\Message as BaseMessage;
use ValuePad\Core\Customer\Persistables\MessagePersistable;
use ValuePad\Core\Appraisal\Persistables\MessagePersistable as BaseMessagePersistable;
use ValuePad\Core\Customer\Validation\MessageValidator;
use ValuePad\Core\User\Entities\User;

class MessageFactoryService extends AbstractMessageFactoryService
{
	/**
	 * @param $senderId
	 * @param $orderId
	 * @param MessagePersistable|BaseMessagePersistable $persistable
	 * @return Message
	 */
	protected function validate($senderId, $orderId, BaseMessagePersistable $persistable)
	{
		(new MessageValidator())->validate($persistable);
	}

	/**
	 * @param int $senderId
	 * @param int $orderId
	 * @param BaseMessagePersistable $persistable
	 * @return BaseMessage
	 */
	protected function instantiate($senderId, $orderId, BaseMessagePersistable $persistable)
	{
		return new Message();
	}

	/**
	 * @param User $sender
	 * @param Order $order
	 * @param BaseMessagePersistable|MessagePersistable $persistable
	 * @param BaseMessage|Message $message
	 */
	protected function exchange(User $sender, Order $order, BaseMessagePersistable $persistable, BaseMessage $message)
	{
		parent::exchange($sender, $order, $persistable, $message);

		$message->setEmployee($persistable->getEmployee());
	}
}
