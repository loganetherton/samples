<?php
namespace ValuePad\Core\Appraisal\Notifications;

interface UpdateProcessStatusNotificationAwareInterface
{
    /**
     * @param UpdateProcessStatusNotification $notification
     */
    public function setUpdateProcessStatusNotification(UpdateProcessStatusNotification $notification);

    /**
     * @return UpdateProcessStatusNotification
     */
    public function getUpdateProcessStatusNotification();
}
