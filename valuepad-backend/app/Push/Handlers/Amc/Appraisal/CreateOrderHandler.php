<?php
namespace ValuePad\Push\Handlers\Amc\Appraisal;
use ValuePad\Core\Appraisal\Notifications\CreateOrderNotification;

class CreateOrderHandler extends BaseHandler
{
    /**
     * @param CreateOrderNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $data['event'] = 'create';

        return $data;
    }
}
