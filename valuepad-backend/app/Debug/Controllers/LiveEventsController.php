<?php
namespace ValuePad\Debug\Controllers;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Container\Container;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\DeclineReason;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Enums\Request;
use ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification;
use ValuePad\Core\Appraisal\Notifications\BidRequestNotification;
use ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification;
use ValuePad\Core\Appraisal\Notifications\CreateOrderNotification;
use ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification;
use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateOrderNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification;
use ValuePad\Core\Appraisal\Objects\Conditions;
use ValuePad\Core\Customer\Entities\AdditionalStatus;
use ValuePad\Core\Customer\Entities\Message;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Core\Log\Extras\Extra;
use ValuePad\Core\Log\Extras\StateExtra;
use ValuePad\Core\Log\Notifications\CreateLogNotification;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Debug\Support\BaseController;
use ValuePad\Live\Support\Notifier;
use DateTime;

class LiveEventsController extends BaseController
{
	/**
	 * @var Notifier
	 */
	private $notifier;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(Notifier $notifier, EntityManagerInterface $entityManager)
	{
		$this->notifier = $notifier;
		$this->entityManager = $entityManager;
	}


	public function orderCreate($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$notification = new CreateOrderNotification($order);

		$this->notifier->notify($notification);
	}

	public function orderUpdate($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$notification = new UpdateOrderNotification($order);

		$this->notifier->notify($notification);
	}

	public function orderDelete($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$notification = new DeleteOrderNotification($order);

		$this->notifier->notify($notification);
	}

	/**
	 * @param int $id
	 */
	public function updateProcessStatus($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$o = new ProcessStatus(ProcessStatus::ACCEPTED);
		$n = new ProcessStatus(ProcessStatus::COMPLETED);

		$notification = new UpdateProcessStatusNotification($order, $o, $n);

		$this->notifier->notify($notification);
	}

	public function changeAdditionalStatus($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$notification = new ChangeAdditionalStatusNotification($order);

		$o = new AdditionalStatus();
		$o->setId(1);
		$o->setActive(true);
		$o->setComment('Old Status');
		$o->setTitle('old-status');

		$n = new AdditionalStatus();
		$n->setId(2);
		$n->setActive(true);
		$n->setComment('New Status');
		$n->setTitle('new-status');

		$notification->setOldAdditionalStatus($o);
		$notification->setNewAdditionalStatus($n);

		$this->notifier->notify($notification);
	}

	public function sendMessage($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$message = new Message();
		$message->setId(1);
		$message->setContent('Hello World!');
		$message->setCreatedAt(new DateTime());
		$message->setOrder($order);
		$message->setSender($order->getCustomer());
		$message->setEmployee('Chuck Norris');

		$notification = new SendMessageNotification($order, $message);

		$this->notifier->notify($notification);
	}

	public function bidRequest($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$notification = new BidRequestNotification($order);

		$this->notifier->notify($notification);
	}

	public function acceptWithConditions($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$conditions = new Conditions();
		$conditions->setDueDate(new DateTime('+10 days'));
		$conditions->setExplanation('I just want it');
		$conditions->setFee(10);
		$conditions->setRequest(new Request(Request::DUE_DATE_EXTENSION));

		$notification = new AcceptOrderWithConditionsNotification($order, $conditions);

		$this->notifier->notify($notification);
	}

	public function decline($id)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$reason = new DeclineReason(DeclineReason::OUT_OF_COVERAGE_AREA);

		$notification = new DeclineOrderNotification($order, $reason);

		$this->notifier->notify($notification);
	}

	public function createLog($id, Container $container)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $id);

		$container->singleton(Session::class, function() use ($order){
			$session = new Session();
			$session->setCreatedAt(new DateTime());
			$session->setUser($order->getCustomer());

			return $session;
		});

		$log = new Log();
		$log->setId(10);
		$log->setOrder($order);
		$log->setAction(new Action(Action::REVISION_REQUEST));
		$log->setAssignee($order->getAssignee());

		$extra = new Extra();
		$extra[Extra::ZIP] = $order->getProperty()->getZip();
		$extra[Extra::STATE] = new StateExtra($order->getProperty()->getState()->getCode(), $order->getProperty()->getState()->getName());
		$extra[Extra::CITY] = $order->getProperty()->getCity();
		$extra[Extra::ADDRESS_1] = $order->getProperty()->getAddress1();
		$extra[Extra::ADDRESS_2] = $order->getProperty()->getAddress2();
		$extra[Extra::CUSTOMER] = $order->getCustomer()->getName();

		$log->setUser($order->getCustomer());
		$log->setCreatedAt(new DateTime());

		$log->setExtra($extra);

		$notification = new CreateLogNotification($log);

		$this->notifier->notify($notification);
	}
}
