<?php
namespace ValuePad\Api\Company\V2_0\Protectors;

use Illuminate\Http\Request;
use ValuePad\Api\Shared\Protectors\AuthProtector;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Session\Entities\Session;

class CompanyAdminProtector extends AuthProtector
{
    /**
     * @return bool
     */
    public function grants()
    {
        if (!parent::grants()) {
            return false;
        }

        /**
         * @var Request $request
         */
        $request = $this->container->make('request');

        $companyId = array_values($request->route()->parameters())[0];

        $session = $this->container->make(Session::class);

        /**
         * @var CompanyService $companyService
         */
        $companyService = $this->container->make(CompanyService::class);

        return $companyService->hasAdmin($companyId, $session->getUser()->getId());
    }
}
