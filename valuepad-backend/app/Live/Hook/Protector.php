<?php
namespace ValuePad\Live\Hook;

use Illuminate\Http\Request;
use ValuePad\Api\Shared\Protectors\AuthProtector;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Session\Entities\Session;

class Protector extends AuthProtector
{
	public function grants()
	{
		if (!parent::grants()){
			return false;
		}

		/**
		 * @var Request $request
		 */
		$request = $this->container->make('request');

		$channel = $request->input('channel_name');

		if (!$channel){
			return false;
		}

		preg_match_all('/\d+/', $channel, $matches);

        $ids = $matches[0] ?? [];


        if (!$ids){
            return false;
        }

		/**
		 * @var Session $session
		 */
		$session = $this->container->make(Session::class);

		$id = $session->getUser()->getId();

        if ($ids[0] != $id){
            return false;
        }

        if ($actAs = ($ids[1] ?? null)){

            /**
             * @var CustomerService $customerService
             */
            $customerService = $this->container->make(CustomerService::class);

            if (!$customerService->isRelatedWithAppraiser($id, $actAs)
                    && !$customerService->isRelatedWithAmc($id, $actAs)){
                return false;
            }
        }

        return true;
    }
}
