<?php
namespace ValuePad\Debug\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class LinkPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'store' => 'all'
		];
	}
}
