<?php
namespace ValuePad\Api\Appraiser\V2_0\Transformers;

use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Shared\Services\AvailabilityPerCustomerService;

class AvailabilityCalculatedProperty
{
    /**
     * @var AvailabilityPerCustomerService
     */
    private $availabilityPerCustomerService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param AvailabilityPerCustomerService $availabilityPerCustomerService
     * @param Session $session
     */
    public function __construct(AvailabilityPerCustomerService $availabilityPerCustomerService, Session $session)
    {
        $this->availabilityPerCustomerService = $availabilityPerCustomerService;
        $this->session = $session;
    }

    /**
     * Returns the default availability for appraisers or the customer specific availability
     * for customers
     *
     * @param Appraiser $appraiser
     * @return Availability|AvailabilityPerCustomer
     */
    public function __invoke(Appraiser $appraiser)
    {
        if ($this->session->getUser() instanceof Customer) {
            return $this->availabilityPerCustomerService->getByUserAndCustomerId(
                $appraiser->getId(), $this->session->getUser()
            );
        }

        return $appraiser->getAvailability();
    }
}
