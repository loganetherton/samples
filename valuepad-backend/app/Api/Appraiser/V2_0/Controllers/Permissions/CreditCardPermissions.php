<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Appraiser\V2_0\Protectors\CustomerProtector;

class CreditCardPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'show' => ['owner', CustomerProtector::class],
			'replace' => 'owner'
		];
	}
}
