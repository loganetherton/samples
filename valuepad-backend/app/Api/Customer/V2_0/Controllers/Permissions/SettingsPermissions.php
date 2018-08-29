<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Customer\V2_0\Protectors\AppraiserProtector;

class SettingsPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'show' => ['owner', AppraiserProtector::class],
			'update' => 'owner'
		];
	}
}
