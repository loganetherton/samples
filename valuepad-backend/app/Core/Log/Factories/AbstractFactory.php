<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Log\Entities\Log;
use DateTime;
use ValuePad\Core\Log\Extras\Extra;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Interfaces\ActorProviderInterface;

abstract class AbstractFactory implements FactoryInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param AbstractNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = new Log();

		/**
		 * @var ActorProviderInterface $actorProvider
		 */
		$actorProvider = $this->container->get(ActorProviderInterface::class);

		$user = $actorProvider->getActor();

		$log->setUser($user);
        $log->setCustomer($notification->getOrder()->getCustomer());
		$log->setAssignee($notification->getOrder()->getAssignee());
		$log->setOrder($notification->getOrder());

		/**
		 * @var EnvironmentInterface $environment
		 */
		$environment = $this->container->get(EnvironmentInterface::class);

		if ($environment->isRelaxed() && $createdAt = $environment->getLogCreatedAt()){
			$log->setCreatedAt($createdAt);
		} else {
			$log->setCreatedAt(new DateTime());
		}

		$extra = new Extra();

		$extra[Extra::USER] = $user->getDisplayName();

		$log->setExtra($extra);

		return $log;
	}


}
