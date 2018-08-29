<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\DeleteOrderNotification;

class DeleteOrderHandler extends BaseHandler
{
    /**
     * @param DeleteOrderNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'delete';

        return $data;
    }
}
