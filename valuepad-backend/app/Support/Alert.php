<?php
namespace ValuePad\Support;

use Illuminate\Container\Container;
use ValuePad\Core\Shared\Interfaces\NotifierInterface;

class Alert implements NotifierInterface
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param object $notification
	 */
	public function notify($notification)
	{
		$listeners = $this->container->make('config')->get('alert.listeners', []);

		foreach ($listeners as $listener){
			/**
			 * @var NotifierInterface $listener
			 */
			$listener = $this->container->make($listener);

			$listener->notify($notification);
		}
	}
}
