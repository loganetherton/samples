<?php
namespace ValuePad\Letter\Handlers\User;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\User\Emails\ResetPasswordEmail;
use ValuePad\Letter\Support\HandlerInterface;
use Illuminate\Config\Repository as Config;

class ResetPasswordEmailHandler implements HandlerInterface
{
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * @param Mailer $mailer
	 * @param ResetPasswordEmail $source
	 */
	public function handle(Mailer $mailer, $source)
	{
		$data = [
			'name' => $source->getToken()->getUser()->getDisplayName(),
			'actionUrl' => $this->config->get('app.front_end_url')
				.'/reset-password?token='.$source->getToken()->getValue(),
		];

		$mailer->queue('emails.users.reset_password', $data, function(Message $message) use ($source) {
			$message->from($source->getSender()->getEmail(), $source->getSender()->getName());

			foreach ($source->getRecipients() as $recipient){
				$message->to($recipient->getEmail(), $recipient->getName());
			}

			$message->subject('Request to Reset Password');
		});
	}
}
