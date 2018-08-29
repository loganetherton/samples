<?php
namespace ValuePad\Letter\Handlers\Appraisal;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification;

class DeclineOrderHandler extends AbstractOrderHandler
{
    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getSubject(AbstractNotification $notification)
    {
        return 'Declined - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'emails.appraisal.decline_order';
    }

    /**
     * @param AbstractNotification|DeclineOrderNotification $notification
     * @return array
     */
    protected function getData(AbstractNotification $notification)
    {
        $data = parent::getData($notification);

        $data['reason'] = ucfirst(str_replace('-', ' ', (string) $notification->getReason()));
        $data['explanation'] = $notification->getMessage();

        return $data;
    }
}
