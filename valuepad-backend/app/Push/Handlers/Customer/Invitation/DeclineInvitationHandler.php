<?php
namespace ValuePad\Push\Handlers\Customer\Invitation;

use ValuePad\Core\Invitation\Notifications\DeclineInvitationNotification;

class DeclineInvitationHandler extends BaseHandler
{
	/**
	 * @param DeclineInvitationNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'invitation',
			'event' => 'decline',
			'invitation' => $notification->getInvitation()->getId()
		];
	}
}
