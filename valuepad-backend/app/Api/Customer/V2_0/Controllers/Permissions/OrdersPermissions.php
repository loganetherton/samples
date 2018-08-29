<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class OrdersPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'update' => 'owner',
			'show' => 'owner',
			'award' => 'owner',
			'destroy' => 'owner',
			'changeAdditionalStatus' => 'owner',
            'payoff' => 'owner'
		];
	}
}
