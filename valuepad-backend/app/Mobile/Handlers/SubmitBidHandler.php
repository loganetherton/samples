<?php
namespace ValuePad\Mobile\Handlers;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use DateTime;

class SubmitBidHandler extends AbstractOrderHandler
{
    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getMessage($notification)
    {
        $property = $notification->getOrder()->getProperty();

        return sprintf('%s has submitted a bid for the order on %s.',
            $this->session->getUser()->getDisplayName(),
            $property->getDisplayAddress()
        );
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return 'submit-bid';
    }

    /**
     * @param AbstractNotification $notification
     * @return array
     */
    protected function getExtra($notification)
    {
        $data = parent::getExtra($notification);

        $bid = $notification->getOrder()->getBid();

        $data['bid'] = [
            'amount' => $bid->getAmount(),
            'estimatedCompletionDate' => null,
            'comments' => $bid->getComments()
        ];

        if ($ecd = $bid->getEstimatedCompletionDate()){
            $data['bid']['estimatedCompletionDate'] = $ecd->format(DateTime::ATOM);
        }

        return $data;
    }
}
