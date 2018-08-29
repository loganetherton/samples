<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\BidRequestNotification;

class BidRequestHandler extends BaseHandler
{
    /**
     * @param BidRequestNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'bid-request';

        return $data;
    }
}
