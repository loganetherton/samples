<?php
namespace ValuePad\Letter\Support;

use Illuminate\Foundation\Bus\DispatchesJobs;
use ValuePad\Core\Support\Letter\EmailerInterface;
use ValuePad\Core\Support\Letter\Email;
use Illuminate\Contracts\Container\Container;

class Emailer implements EmailerInterface
{
	use DispatchesJobs;

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
	 * @param Email $email
	 */
	public function send(Email $email)
	{
		$handler = $this->container->make('config')->get('mail.emailer')[get_class($email)];

		/**
		 * @var HandlerInterface $handler
		 */
		$handler = $this->container->make($handler);

		$handler->handle($this->container->make('mailer'), $email);
	}
}
