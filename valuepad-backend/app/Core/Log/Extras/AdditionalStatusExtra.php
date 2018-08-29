<?php
namespace ValuePad\Core\Log\Extras;

use ValuePad\Core\Customer\Entities\AdditionalStatus;

class AdditionalStatusExtra extends Extra
{
	/**
	 * @param AdditionalStatus $additionalStatus
	 * @return static
	 */
	public static function fromAdditionalStatus(AdditionalStatus $additionalStatus)
	{
		$extra = new static();

		$extra[Extra::TITLE] = $additionalStatus->getTitle();
		$extra[Extra::COMMENT] = $additionalStatus->getComment();

		return $extra;
	}
}
