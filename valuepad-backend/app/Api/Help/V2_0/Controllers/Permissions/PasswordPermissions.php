<?php
namespace ValuePad\Api\Help\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class PasswordPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'reset' => 'all',
			'change' => 'all'
		];
	}
}
