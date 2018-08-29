<?php
namespace ValuePad\Core\Invitation\Notifications;

use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Invitation\Entities\Invitation;

class AcceptInvitationNotification extends AbstractNotification
{
	/**
	 * @var Appraiser $appraiser
	 */
	private $appraiser;

	/**
	 * @param Invitation $invitation
	 * @param Appraiser $appraiser
	 */
	public function __construct(Invitation $invitation, Appraiser $appraiser)
	{
		parent::__construct($invitation);
		$this->appraiser = $appraiser;
	}

	/**
	 * @return Appraiser
	 */
	public function getAppraiser()
	{
		return $this->appraiser;
	}
}
