<?php
namespace ValuePad\Letter\Handlers\Appraisal;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use ValuePad\Core\Appraisal\Emails\UnacceptedReminderEmail;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Letter\Support\HandlerInterface;
use Illuminate\Config\Repository as Config;

class UnacceptedReminderEmailHandler implements HandlerInterface
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
     * @param UnacceptedReminderEmail $source
     */
    public function handle(Mailer $mailer, $source)
    {
        $order = $source->getOrder();
        $assignee = $order->getAssignee();

        $data = [
            'greeting' => $assignee instanceof Appraiser ? 'Hello '.$assignee->getFirstName().', ' : 'Hello,',
            'fileNumber' => $order->getFileNumber(),
            'loanNumber' => $order->getLoanNumber(),
            'borrower' => object_take($order, 'borrower.displayName', ''),
            'actionUrl' => $this->config->get('app.front_end_url').'/orders/details/'.$order->getId(),
            'hours' => $order->getCustomer()->getSettings()->getUnacceptedReminder()
        ];

        $mailer->queue('emails.appraisal.unaccepted_reminder', $data, function(Message $message) use ($source, $order){

            $message->from($source->getSender()->getEmail(), $source->getSender()->getName());

            $recipient = $source->getRecipients()[0];

            $message->to($recipient->getEmail(), $recipient->getName());

            $message->subject('Unaccepted Reminder - Order#: '.$order->getFileNumber());
        });
    }
}
