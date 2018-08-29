<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Assignee\V2_0\Protectors\CustomerByOrderProtector;

class MessagesPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'store' => 'owner',
			'indexByOrder' => ['owner', CustomerByOrderProtector::class],
			'index' => 'owner',
			'show' => 'owner',
			'markAsRead' => 'owner',
			'markSomeAsRead' => 'owner',
			'markAllAsRead' => 'owner',
			'total' => 'owner'
		];
	}
}
