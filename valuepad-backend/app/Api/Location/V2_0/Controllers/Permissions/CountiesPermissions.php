<?php
namespace ValuePad\Api\Location\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class CountiesPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'index' => 'all',
			'show' => 'all'
		];
	}
}
