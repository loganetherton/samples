<?php
namespace ValuePad\Api\Invitation\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Invitation\Entities\Invitation;

class InvitationTransformer extends BaseTransformer
{
	/**
	 * @param Invitation $invitation
	 * @return array
	 */
	public function transform($invitation)
	{
		return $this->extract($invitation);
	}
}
