<?php
namespace ValuePad\Live\Handlers;
use ValuePad\Core\Appraisal\Notifications\SubmitBidNotification;

class SubmitBidHandler extends AbstractOrderHandler
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'submit-bid';
    }

    /**
     * @param SubmitBidNotification $notification
     * @return array
     */
    protected function getData($notification)
    {
        return [
            'order' => $this->transformer()->transform($notification->getOrder()),
            'bid' => $this->transformer()->transform($notification->getOrder()->getBid())
        ];
    }
}
