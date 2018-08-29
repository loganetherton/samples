<?php
namespace ValuePad\Letter\Handlers\Appraisal;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;

class ReconsiderationRequestHandler extends AbstractOrderHandler
{
    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getSubject(AbstractNotification $notification)
    {
        return 'Reconsideration Request - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'emails.appraisal.reconsideration_request';
    }

    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getActionUrl(AbstractNotification $notification)
    {
        return $this->config->get('app.front_end_url').'/orders/details/'.$notification->getOrder()->getId().'/revisions';
    }
}
