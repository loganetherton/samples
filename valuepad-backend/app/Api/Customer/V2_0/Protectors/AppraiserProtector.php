<?php
namespace ValuePad\Api\Customer\V2_0\Protectors;

use Illuminate\Http\Request;
use ValuePad\Api\Shared\Protectors\AuthProtector;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Session\Entities\Session;

class AppraiserProtector extends AuthProtector
{
	/**
	 * @return bool
	 */
	public function grants()
	{
		if (!parent::grants()){
			return false;
		}

		/**
		 * @var Request $request
		 */
		$request = $this->container->make('request');

		$customerId = (int) array_values($request->route()->parameters())[0];

		/**
		 * @var Session $session
		 */
		$session = $this->container->make(Session::class);

		/**
		 * @var CustomerService $customerService
		 */
		$customerService = $this->container->make(CustomerService::class);

		return $customerService->isRelatedWithAppraiser($customerId, $session->getUser()->getId());
	}
}
