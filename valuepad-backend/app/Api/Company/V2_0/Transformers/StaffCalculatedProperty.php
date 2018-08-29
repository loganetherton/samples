<?php
namespace ValuePad\Api\Company\V2_0\Transformers;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Company\Services\StaffService;
use ValuePad\Core\Session\Entities\Session;

class StaffCalculatedProperty
{
    /**
     * @var StaffService
     */
    private $staffService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param StaffService $staffService
     * @param Session $session
     */
    public function __construct(StaffService $staffService, Session $session)
    {
        $this->staffService = $staffService;
        $this->session = $session;
    }

    /**
     * @param Company $company
     * @return Staff
     */
    public function __invoke(Company $company)
    {
        return $this->staffService->getByCompanyAndUserIds($company->getId(), $this->session->getUser()->getId());
    }
}
