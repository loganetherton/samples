<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\AwardOrderNotification;

class AwardOrderHandler extends BaseHandler
{
    /**
     * @param AwardOrderNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'award';

        return $data;
    }
}
