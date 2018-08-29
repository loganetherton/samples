<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Assignee\V2_0\Protectors\CustomerByOrderProtector;

class OrdersPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'index' => 'owner',
			'show' => ['owner', CustomerByOrderProtector::class],
			'accept' => 'owner',
			'acceptWithConditions' => 'owner',
			'decline' => 'owner',
			'totals' => 'owner',
			'pay' => 'owner',
			'listAdditionalStatuses' => ['owner', CustomerByOrderProtector::class],
			'changeAdditionalStatus' => 'owner',
			'reassign' => 'owner',
			'accounting' => 'owner',
		];
	}
}
