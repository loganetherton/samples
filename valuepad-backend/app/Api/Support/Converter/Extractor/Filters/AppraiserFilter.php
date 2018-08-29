<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use Ascope\Libraries\Converter\Extractor\Root;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Support\Shortcut;

class AppraiserFilter extends AbstractFilter
{
    /**
     * @param string $key
     * @param Appraiser $object
     * @param Root|null $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null)
    {
        if (in_array($this->getRoute(), [
                Shortcut::prependGlobalRoutePrefix('v2.0/sessions'),
                Shortcut::prependGlobalRoutePrefix('v2.0/appraisers')
            ]) && $this->isPost()){
            return true;
        }

        if ($object->getId() === object_take($this->session, 'user.id')){
            return true;
        }

        $user = $this->session->getUser();

        if ($user instanceof Customer){
            /**
             * @var CustomerService $customerService
             */
            $customerService = $this->container->make(CustomerService::class);

            if ($customerService->isRelatedWithAppraiser($user->getId(), $object->getId())){
                return true;
            }
        }

        if (mb_strpos($this->getRoute(), Shortcut::prependGlobalRoutePrefix('v2.0/companies')) !== false) {
            $companyId = $this->request->route('companyId') ?? $this->request->route('companies');

            if ($companyId) {
                $companyService = $this->container->make(CompanyService::class);

                if ($companyService->hasStaffAsUser($companyId, $object->getId())
                    && $companyService->hasManager($companyId, $user->getId())) {
                    return true;
                }
            }
        }

        if (in_array($key, ['w9', 'taxIdentificationNumber'])){
            return false;
        }

        return true;
    }
}
