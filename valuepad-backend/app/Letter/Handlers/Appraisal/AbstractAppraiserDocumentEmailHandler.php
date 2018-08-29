<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface;
use ValuePad\Core\Support\Letter\Email;
use ValuePad\Letter\Support\HandlerInterface;

abstract class AbstractAppraiserDocumentEmailHandler implements HandlerInterface
{
	/**
	 * @var DocumentPreferenceInterface
	 */
	private $preference;

	/**
	 * @param DocumentPreferenceInterface $preference
	 */
	public function __construct(DocumentPreferenceInterface $preference)
	{
		$this->preference = $preference;
	}

	/**
	 * @param Mailer $mailer
	 * @param Email $source
	 */
	public function handle(Mailer $mailer, $source)
	{
		$appraiser = $this->getOrder($source)->getAssignee();

		$data = [
			'firstName' => $appraiser->getFirstName(),
			'lastName' => $appraiser->getLastName(),
			'fileNumber' => $this->getOrder($source)->getFileNumber(),
			'document' => [
				'url' => $this->preference->getBaseUrl().$this->getDocument($source)->getUri(),
				'name' => $this->getDocument($source)->getName()
			]
		];

		$order = $this->getOrder($source);

		$mailer->queue('emails.appraisal.appraiser_document', $data, function(Message $message) use ($source, $order) {
			$message->from($source->getSender()->getEmail(), $source->getSender()->getName());

			foreach ($source->getRecipients() as $recipient){
				$message->to($recipient->getEmail(), $recipient->getName());
			}

			$message->subject('Documents - Order#: '.$order->getFileNumber());
		});
	}

	/**
	 * @param Email $source
	 * @return Document
	 */
	abstract protected function getDocument($source);

	/**
	 * @param Email $source
	 * @return Order
	 */
	abstract protected function getOrder($source);
}
