<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\UpdateOrderNotification;

class UpdateOrderHandler extends BaseHandler
{
    /**
     * @param UpdateOrderNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'update';

        return $data;
    }
}
