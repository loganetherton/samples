<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;

class SendMessageHandler extends BaseHandler
{
    /**
     * @param SendMessageNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'send-message';
        $data['message'] = $notification->getMessage()->getId();

        return $data;
    }
}
