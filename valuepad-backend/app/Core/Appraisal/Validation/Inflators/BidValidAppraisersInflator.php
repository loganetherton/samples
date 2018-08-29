<?php
namespace ValuePad\Core\Appraisal\Validation\Inflators;

use Ascope\Libraries\Validation\Error;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Validation\Rules\AppraiserInCompany;
use ValuePad\Core\User\Validation\Rules\UserIsAppraiser;

class BidValidAppraisersInflator
{
    /**
     * @var UserIsAppraiser
     */
    private $userIsAppraiser;

    /**
     * @var AppraiserInCompany
     */
    private $appraiserInCompany;

    /**
     * @param UserIsAppraiser $userIsAppraiser
     * @param AppraiserInCompany $appraiserInCompany
     */
    public function __construct(UserIsAppraiser $userIsAppraiser, AppraiserInCompany $appraiserInCompany)
    {
        $this->userIsAppraiser = $userIsAppraiser;
        $this->appraiserInCompany = $appraiserInCompany;
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param int $value
     * @return null|Error
     */
    public function check($value)
    {
        if ($error = $this->userIsAppraiser->check($value)) {
            return $error;
        }

        if ($error = $this->appraiserInCompany->check($value)) {
            return $error;
        }

        return null;
    }
}
