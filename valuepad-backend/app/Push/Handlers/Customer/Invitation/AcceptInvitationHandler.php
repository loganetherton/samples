<?php
namespace ValuePad\Push\Handlers\Customer\Invitation;

use ValuePad\Core\Invitation\Notifications\AcceptInvitationNotification;

class AcceptInvitationHandler extends BaseHandler
{
	/**
	 * @param AcceptInvitationNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'invitation',
			'event' => 'accept',
			'invitation' => $notification->getInvitation()->getId(),
			'appraiser' => $notification->getAppraiser()->getId()
		];
	}
}
