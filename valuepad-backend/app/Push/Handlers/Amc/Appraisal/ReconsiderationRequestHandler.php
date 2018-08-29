<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\ReconsiderationRequestNotification;

class ReconsiderationRequestHandler extends BaseHandler
{
    /**
     * @param ReconsiderationRequestNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'reconsideration-request';
        $data['reconsideration'] = $notification->getReconsideration()->getId();

        return $data;
    }
}
