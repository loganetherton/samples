<?php
namespace ValuePad\Mobile\Support;

use Illuminate\Container\Container;
use Illuminate\Foundation\Bus\DispatchesJobs;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;

class Notifier implements NotifierInterface
{
	use DispatchesJobs;

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var EnvironmentInterface
	 */
	private $environment;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->environment = $container->make(EnvironmentInterface::class);
	}

	/**
	 * @param object $notification
	 */
	public function notify($notification)
	{
		$handlers = $this->container->make('config')->get('alert.mobile.handlers', []);

		$class = get_class($notification);

		if (!isset($handlers[$class])){
			return ;
		}

		/**
		 * @var HandlerInterface $handler
		 */
		$handler = $this->container->make($handlers[$class]);

		$tuple = $handler->handle($notification);

		if ($tuple === null){
			return ;
		}

		$job = new Job($tuple);

		$this->dispatch($job);
	}
}
