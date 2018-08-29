<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class DocumentsPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'index' => 'owner',
			'show' => 'owner',
			'store' => 'owner',
			'destroy' => 'owner',
			'update' => 'owner'
		];
	}
}
