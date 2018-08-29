<?php
namespace ValuePad\Letter\Handlers\Help;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\Help\Emails\HelpEmail;
use ValuePad\Letter\Support\HandlerInterface;

abstract class HelpEmailHandler implements HandlerInterface
{
	/**
	 * @param Mailer $mailer
	 * @param HelpEmail $source
	 */
	public function handle(Mailer $mailer, $source)
	{
		$subject = $this->getSubject();

		$mailer->queue('emails.help.default', ['description' => $source->getDescription(), 'sender' => $source->getSender()->getName()],
			function(Message $message) use ($source, $subject) {
				$recipient = $source->getRecipients()[0];

				$message->from($source->getSender()->getEmail(), $source->getSender()->getName());
				$message->to($recipient->getEmail(), $recipient->getName());
				$message->subject($subject);
			});
	}

	/**
	 * @return string
	 */
	abstract protected function getSubject();
}
