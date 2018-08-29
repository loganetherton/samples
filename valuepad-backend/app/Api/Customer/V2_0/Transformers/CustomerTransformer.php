<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Customer\Entities\Customer;

class CustomerTransformer extends BaseTransformer
{
	/**
	 * @param Customer $customer
	 * @return array
	 */
	public function transform($customer)
	{
		return $this->extract($customer);
	}
}
