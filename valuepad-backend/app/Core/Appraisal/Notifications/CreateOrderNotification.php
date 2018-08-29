<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Invitation\Entities\Invitation;

class CreateOrderNotification extends AbstractNotification
{
	/**
	 * @var Invitation
	 */
	private $invitation;

	/**
	 * @param Invitation $invitation
	 */
	public function withInvitation(Invitation $invitation)
	{
		$this->invitation = $invitation;
	}

	/**
	 * @return Invitation
	 */
	public function getInvitation()
	{
		return $this->invitation;
	}
}
