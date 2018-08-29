<?php
namespace ValuePad\Letter\Support;

use Swift_Events_EventListener;
use Swift_Mime_Message;
use Swift_Transport;

class PigeonTransport implements Swift_Transport
{
	/**
	 * @var Swift_Transport
	 */
	private $transport;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @param Swift_Transport $transport
	 * @param array $config
	 */
	public function __construct(Swift_Transport $transport, array $config)
	{
		$this->transport = $transport;
		$this->config = $config;
	}

	/**
	 * Test if this Transport mechanism has started.
	 *
	 * @return bool
	 */
	public function isStarted()
	{
		return $this->transport->isStarted();
	}

	/**
	 * Start this Transport mechanism.
	 */
	public function start()
	{
		$this->transport->start();
	}

	/**
	 * Stop this Transport mechanism.
	 */
	public function stop()
	{
		$this->transport->stop();
	}

	/**
	 * Send the given Message.
	 *
	 * Recipient/sender data will be retrieved from the Message API.
	 * The return value is the number of recipients who were accepted for delivery.
	 *
	 * @param Swift_Mime_Message $message
	 * @param string[] $failedRecipients An array of failures by-reference
	 *
	 * @return int
	 */
	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		if (array_take($this->config, 'filter', false) === true
			&& !$this->isMailAllowed(key($message->getTo()))){
			return -1;
		}

		return $this->transport->send($message, $failedRecipients);
	}

	/**
	 * @param string $email
	 * @return bool
	 */
	private function isMailAllowed($email)
	{
		$patterns = array_take($this->config, 'whitelist', []);

		foreach ($patterns as $pattern){
			if (preg_match('/^'.$pattern.'$/', $email)){
				return true;
			}
		}

		return false;
	}

	/**
	 * Register a plugin in the Transport.
	 *
	 * @param Swift_Events_EventListener $plugin
	 */
	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
		$this->transport->registerPlugin($plugin);
	}
}
