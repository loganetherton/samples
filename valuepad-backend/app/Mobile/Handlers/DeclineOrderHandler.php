<?php
namespace ValuePad\Mobile\Handlers;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification;

class DeclineOrderHandler extends AbstractOrderHandler
{
    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getMessage($notification)
    {
        $property = $notification->getOrder()->getProperty();

        return sprintf('%s has declined the order on %s.',
            $this->session->getUser()->getDisplayName(),
            $property->getDisplayAddress()
        );
    }

    /**
     * @param AbstractNotification|DeclineOrderNotification $notification
     * @return array
     */
    protected function getExtra($notification)
    {
        $data = parent::getExtra($notification);

        $data['reason'] = (string) $notification->getReason();
        $data['message'] = $notification->getMessage();

        return $data;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return 'decline';
    }
}
