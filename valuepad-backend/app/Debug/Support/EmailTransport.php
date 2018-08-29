<?php
namespace ValuePad\Debug\Support;

use Ascope\QA\Support\Browser;
use Ascope\QA\Support\Request;
use Illuminate\Contracts\Container\Container;
use Illuminate\Mail\Transport\Transport;
use Swift_Mime_Message;

class EmailTransport extends Transport
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
		$url = $this->container->make('config')->get('app.url');

		$browser = new Browser($url);

		$request = Request::post('/debug/emails', [
			'from' => $message->getFrom(),
			'to' => $message->getTo(),
			'subject' => $message->getSubject(),
			'contents' => $message->getBody()

		]);

		$browser->send($request);

		return 1;
	}
}
