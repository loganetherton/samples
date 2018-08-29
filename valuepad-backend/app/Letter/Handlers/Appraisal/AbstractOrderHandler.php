<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use Illuminate\Container\Container;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Assignee\Services\NotificationSubscriptionService;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Letter\Support\HandlerInterface;
use ValuePad\Core\Support\Letter\LetterPreferenceInterface;
use Illuminate\Config\Repository as Config;

abstract class AbstractOrderHandler implements HandlerInterface
{
	/**
	 * @var LetterPreferenceInterface
	 */
	protected $preference;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var NotificationSubscriptionService
	 */
	private $subscriptionService;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;

		$this->preference = $container->make(LetterPreferenceInterface::class);
		$this->subscriptionService = $container->make(NotificationSubscriptionService::class);
		$this->config = $container->make('config');
		$this->session = $container->make(Session::class);
	}

	/**
	 * @param Mailer $mailer
	 * @param AbstractNotification $source
	 */
	public function handle(Mailer $mailer, $source)
	{
        if (!$this->shouldNotify($source)){
            return ;
        }

		$order = $source->getOrder();
		$assignee = $order->getAssignee();

        if (!$assignee->getStatus()->is(Status::APPROVED)){
            return ;
        }

		$subscription = $this->subscriptionService->getByCustomerId(
			$assignee->getId(), $order->getCustomer()->getId());

		if (object_take($subscription, 'email', true) === false){
			return ;
		}

		$noReplay = $this->preference->getNoReply();
		$subject = $this->getSubject($source);

		$mailer->queue($this->getTemplate(), $this->getData($source),

			// Due to the fact that Laravel has some limitations when serializing closures
			// it is preferable to not use $this inside the anonymous function to avoid errors.
			function(Message $message) use ($assignee, $source, $noReplay, $subject) {
				$message->from($noReplay, 'The ValuePad Team');
				$message->to($assignee->getEmail(), $assignee->getDisplayName());
				$message->subject($subject);
			});
	}

    /**
     * @param AbstractNotification $source
     * @return bool
     */
	protected function shouldNotify(AbstractNotification $source)
    {
        /**
         * @var Session $session
         */
        $session = $this->container->make(Session::class);

        $user = $session->getUser();

        return !$user instanceof Appraiser && !$user instanceof Amc;
    }

	/**
	 * @param AbstractNotification $notification
	 * @return string
	 */
	abstract protected function getSubject(AbstractNotification $notification);

	/**
	 * @return string
	 */
	abstract protected function getTemplate();

	/**
	 * @param AbstractNotification $notification
	 * @return array
	 */
	protected function getData(AbstractNotification $notification)
	{
		$order = $notification->getOrder();
		$assignee = $order->getAssignee();

		return [
			'user' => $this->getUser(),
			'greeting' => $assignee instanceof Appraiser ? 'Hello '.$assignee->getFirstName().', ' : 'Hello,',
			'fileNumber' => $order->getFileNumber(),
			'loanNumber' => $order->getLoanNumber(),
			'address' => $order->getProperty()->getDisplayAddress(),
			'borrower' => object_take($order, 'borrower.displayName', ''),
			'customer' => $order->getCustomer()->getName(),
			'actionUrl' => $this->getActionUrl($notification)
		];
	}

	/**
	 * @return string
	 */
	protected function getUser()
	{
		return $this->session->getUser()->getDisplayName() ?? 'Unknown';
	}

	/**
	 * @param AbstractNotification $notification
	 * @return string
	 */
	protected function getActionUrl(AbstractNotification $notification)
	{
		return $this->config->get('app.front_end_url').'/orders/details/'.$notification->getOrder()->getId();
	}
}
