<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Assignee\V2_0\Protectors\CustomerByOrderProtector;

class DocumentPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'formats' => ['owner', CustomerByOrderProtector::class],
			'store' => 'owner',
			'update' => 'owner',
			'show' => ['owner', CustomerByOrderProtector::class],
			'email' => 'owner'
		];
	}
}
