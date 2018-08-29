<?php
namespace ValuePad\Mobile\Handlers;

use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;

class SendMessageHandler extends AbstractOrderHandler
{
    /**
     * @param SendMessageNotification $notification
     * @return string
     */
    protected function getMessage($notification)
    {
        $message = $notification->getMessage();
        $order = $notification->getOrder();

        return sprintf(
            '%s sent a message on order - %s',
            $message->getSender()->getDisplayName() ?? 'Unknown',
            $order->getFileNumber()
        );
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return 'send-message';
    }
}
