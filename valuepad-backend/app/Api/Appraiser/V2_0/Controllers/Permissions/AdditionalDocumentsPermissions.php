<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Assignee\V2_0\Protectors\CustomerByOrderProtector;

class AdditionalDocumentsPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'types' => ['owner', CustomerByOrderProtector::class],
			'store' => 'owner',
			'index' => ['owner', CustomerByOrderProtector::class],
			'email' => 'owner'
		];
	}
}
