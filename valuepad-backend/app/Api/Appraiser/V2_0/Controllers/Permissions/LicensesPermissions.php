<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class LicensesPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'index' => 'auth',
			'show' => 'auth',
			'store' => 'owner',
			'update' => 'owner',
			'destroy' => 'owner'
		];
	}
}
