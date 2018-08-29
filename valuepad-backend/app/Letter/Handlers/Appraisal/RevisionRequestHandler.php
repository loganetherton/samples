<?php
namespace ValuePad\Letter\Handlers\Appraisal;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;

class RevisionRequestHandler extends AbstractOrderHandler
{
    /**
     * @param AbstractNotification $notification
     * @return string
     */
    protected function getSubject(AbstractNotification $notification)
    {
        return 'Revision Request - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'emails.appraisal.revision_request';
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
