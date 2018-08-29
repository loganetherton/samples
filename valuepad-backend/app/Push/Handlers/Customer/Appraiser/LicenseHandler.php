<?php
namespace ValuePad\Push\Handlers\Customer\Appraiser;
use ValuePad\Core\Appraiser\Notifications\AbstractLicenseNotification;
use ValuePad\Core\Appraiser\Notifications\DeleteLicenseNotification;
use ValuePad\Core\Appraiser\Notifications\UpdateLicenseNotification;

class LicenseHandler extends AbstractAppraiserHandler
{
    /**
     * @param AbstractLicenseNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = parent::transform($notification);

        $event = 'create-license';

        if ($notification instanceof UpdateLicenseNotification){
            $event = 'update-license';
        }

        if ($notification instanceof DeleteLicenseNotification){
            $event = 'delete-license';
        }

        $data['license'] = $notification->getLicense()->getId();
        $data['event'] = $event;

        return $data;
    }
}
