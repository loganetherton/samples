<?php
namespace ValuePad\Core\Appraiser\Services;

use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Assignee\Services\CustomerFeeService as AbstractCustomerFeeService;

class CustomerFeeService extends AbstractCustomerFeeService
{
	protected function getAssigneeClass()
	{
		return Appraiser::class;
	}

	protected function getAssigneeServiceClass()
	{
		return AppraiserService::class;
	}
}
