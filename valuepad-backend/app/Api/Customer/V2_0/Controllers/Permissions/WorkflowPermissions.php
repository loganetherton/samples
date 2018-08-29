<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;

class WorkflowPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		$values = ProcessStatus::toArray();

		$actions = [];

		foreach ($values as $value){

			$actions[camel_case($value === 'new' ? 'fresh' : $value)] = 'owner';
		}

		return $actions;
	}
}
