<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Assignee\V2_0\Protectors\CustomerByOrderProtector;

class RevisionsPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'index' => ['owner', CustomerByOrderProtector::class]
		];
	}
}
