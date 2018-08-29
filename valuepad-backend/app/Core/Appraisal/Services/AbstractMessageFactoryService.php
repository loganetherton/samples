<?php
namespace ValuePad\Core\Appraisal\Services;

use ValuePad\Core\Appraisal\Entities\Message;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;
use ValuePad\Core\Appraisal\Options\CreateMessageOptions;
use ValuePad\Core\Appraisal\Persistables\MessagePersistable;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Support\Service\AbstractService;
use DateTime;
use ValuePad\Core\User\Entities\User;

abstract class AbstractMessageFactoryService extends AbstractService
{
	/**
	 * @param User $sender
	 * @param Order $order
	 * @param MessagePersistable $persistable
	 * @param Message $message
	 */
	protected function exchange(User $sender, Order $order, MessagePersistable $persistable, Message $message)
	{
		$message->setContent($persistable->getContent());

		if ($createdAt = $this->environment->getLogCreatedAt()){
			$message->setCreatedAt($createdAt);
		} else {
			$message->setCreatedAt(new DateTime());
		}

		$message->setOrder($order);
		$message->setSender($sender);
	}


	/**
	 * @param int $senderId
	 * @param int $orderId
	 * @param MessagePersistable $persistable
	 * @param CreateMessageOptions $options
	 * @return Message
	 */
	public function create($senderId, $orderId, MessagePersistable $persistable, CreateMessageOptions $options = null)
	{
		if ($options === null){
			$options = new CreateMessageOptions();
		}

		if (!$options->isTrusted()){
			$this->validate($senderId, $orderId, $persistable);
		}

		$message = $this->instantiate($senderId, $orderId, $persistable);

		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->getReference(Order::class, $orderId);

		/**
		 * @var User $sender
		 */
		$sender = $this->entityManager->getReference(User::class, $senderId);

		$this->exchange($sender, $order, $persistable, $message);

		$this->entityManager->persist($message);

		$this->entityManager->flush();

		$message->addReader($sender);

		if ($this->environment->isRelaxed() && !$sender instanceof Appraiser){
			$message->addReader($order->getAssignee());
		}

		$this->entityManager->flush();

		$this->notify(new SendMessageNotification($order, $message));

		return $message;
	}

	/**
	 * @param $senderId
	 * @param $orderId
	 * @param MessagePersistable $persistable
	 */
	abstract protected function validate($senderId, $orderId, MessagePersistable $persistable);

	/**
	 * @param int $senderId
	 * @param int $orderId
	 * @param MessagePersistable $persistable
	 * @return Message
	 */
	abstract protected function instantiate($senderId, $orderId, MessagePersistable $persistable);
}
