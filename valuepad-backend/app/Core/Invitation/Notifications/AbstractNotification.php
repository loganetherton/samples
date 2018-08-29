<?php
namespace ValuePad\Core\Invitation\Notifications;

use ValuePad\Core\Invitation\Entities\Invitation;

abstract class AbstractNotification
{
	/**
	 * @var Invitation
	 */
	private $invitation;

	/**
	 * @param Invitation $invitation
	 */
	public function __construct(Invitation $invitation)
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
