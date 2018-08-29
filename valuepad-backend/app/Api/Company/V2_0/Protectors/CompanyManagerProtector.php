<?php
namespace ValuePad\Api\Company\V2_0\Protectors;

use Illuminate\Http\Request;
use ValuePad\Api\Shared\Protectors\AuthProtector;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Session\Entities\Session;

class CompanyManagerProtector extends AuthProtector
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

        $companyId = $request->route()->parameter('companyId');

        if (! $companyId) {
            // $route->resource() compatibility
            $companyId = $request->route()->parameter('companies');
        }

        $session = $this->container->make(Session::class);

        $companyService = $this->container->make(CompanyService::class);

        return $companyService->hasManager($companyId, $session->getUser()->getId());
    }
}
