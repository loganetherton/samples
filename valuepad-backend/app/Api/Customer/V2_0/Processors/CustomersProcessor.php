<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Customer\Enums\CompanyType;
use ValuePad\Core\Customer\Persistables\CustomerPersistable;

class CustomersProcessor extends BaseProcessor
{
	/**
	 * @return array
	 */
	public function configuration()
	{
		$config =  [
			'username' => 'string',
			'name' => 'string',
			'phone' => 'string',
			'companyType' => new Enum(CompanyType::class),
		];

		if (!$this->isPatch()){
			$config['password'] = 'string';
		}

		return $config;
	}

	/**
	 * @return CustomerPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new CustomerPersistable());
	}
}
