<?php
namespace ValuePad\Letter\Handlers\Appraisal;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\SubmitBidNotification;

class SubmitBidHandler extends AbstractOrderHandler
{
    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getSubject(AbstractNotification $notification)
    {
        return 'Bid Submitted - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'emails.appraisal.submit_bid';
    }

    /**
     * @param AbstractNotification|SubmitBidNotification $notification
     * @return array
     */
    protected function getData(AbstractNotification $notification)
    {
        $data = parent::getData($notification);

        $bid = $notification->getOrder()->getBid();

        $data['bid'] = [
            'amount' => $bid->getAmount(),
            'ecd' => null,
            'comments' => $bid->getComments()
        ];

        if ($ecd = $bid->getEstimatedCompletionDate()){
            $data['bid']['ecd'] = $ecd->format('m/d/Y');
        }

        return $data;
    }
}
