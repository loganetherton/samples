<?php
namespace ValuePad\Push\Handlers\Customer\Appraiser;

use ValuePad\Core\Appraiser\Notifications\UpdateAppraiserNotification;

class UpdateAppraiserHandler extends AbstractAppraiserHandler
{
	/**
	 * @param UpdateAppraiserNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		$data = parent::transform($notification);

        $data['event'] = 'update';

        return $data;
	}
}
