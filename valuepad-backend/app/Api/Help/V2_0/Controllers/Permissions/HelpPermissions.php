<?php
namespace ValuePad\Api\Help\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class HelpPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'storeIssues' => 'auth',
			'storeFeatureRequests' => 'auth',
			'hints' => 'all'
		];
	}
}
