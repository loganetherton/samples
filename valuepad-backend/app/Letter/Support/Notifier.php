<?php
namespace ValuePad\Letter\Support;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification as OrderNotification;
use ValuePad\Core\Appraisal\Notifications\CreateOrderNotification;
use ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;

class Notifier implements NotifierInterface
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var callable[]
	 */
	private $filters = [];

	/**
	 * @var EnvironmentInterface
	 */
	private $environment;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->environment = $container->make(EnvironmentInterface::class);
		$this->entityManager = $container->make(EntityManagerInterface::class);
		$this->config = $container->make('config');
	}

	/**
	 * @param object $notification
	 */
	public function notify($notification)
	{
		foreach ($this->filters as $filter){
			if ($filter($notification) === null){
				return ;
			}
		}

		/**
		 * We don't want to send emails to real appraisers when importing data from Appraisal Scope
		 */
		if ($this->environment->isRelaxed()){
			return ;
		}

		if ($this->isTooFrequent($notification)){
			return ;
		}

		$handlers = $this->container->make('config')->get('alert.letter.handlers', []);

		$class = get_class($notification);

		if (!isset($handlers[$class])){
			return ;
		}

		/**
		 * @var HandlerInterface $handler
		 */
		$handler = $this->container->make($handlers[$class]);

		$handler->handle($this->container->make('mailer'), $notification);
	}

	/**
	 * @param object $notification
	 * @return bool
	 */
	private function isTooFrequent($notification)
	{
		$config = $this->config->get('app.emails_frequency_tracker');

		if (!$config['enabled']){
			return false;
		}

		if (!$notification instanceof OrderNotification){
			return false;
		}

		/**
		 * We should always notify the user about that the order has been deleted.
		 * The frequency record will be deleted from the database with the order.
		 */
		if ($notification instanceof DeleteOrderNotification){
			return false;
		}

		$alias = snake_case(cut_string_right(short_name(get_class($notification)), 'Notification'), '-');

		if ($notification instanceof CreateOrderNotification){

			$frequency = new Frequency();
			$frequency->setOrder($notification->getOrder());
			$frequency->setAlias($alias);

			$this->entityManager->persist($frequency);
			$this->entityManager->flush();

			return false;
		}

		/**
		 * @var Frequency $frequency
		 */
		$frequency = $this->entityManager->getRepository(Frequency::class)
			->findOneBy(['order' => $notification->getOrder()->getId()]);

		if ($frequency === null){
			return false;
		}

		if ((new DateTime('-'.$config['waiting_time'].' seconds')) >= $frequency->getUpdatedAt()) {
			return false;
		}

		$frequency->setAlias($alias);
		$frequency->setUpdatedAt(new DateTime());

		$this->entityManager->flush();

		return true;
	}


	/**
	 * @param callable $filter
	 */
	public function addFilter(callable  $filter)
	{
		$this->filters[] = $filter;
	}
}
