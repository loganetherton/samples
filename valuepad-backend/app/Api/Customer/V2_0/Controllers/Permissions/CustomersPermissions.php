<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Customer\V2_0\Protectors\AppraiserProtector;

class CustomersPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'store' => 'all',
			'show' => ['owner', AppraiserProtector::class],
			'update' => 'owner'
		];
	}
}
