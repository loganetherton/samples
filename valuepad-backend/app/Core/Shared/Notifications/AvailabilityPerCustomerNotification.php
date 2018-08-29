<?php
namespace ValuePad\Core\Shared\Notifications;

use ValuePad\Core\Shared\Entities\AvailabilityPerCustomer;

class AvailabilityPerCustomerNotification
{
    /**
     * @var AvailabilityPerCustomer
     */
    private $availability;

    /**
     * @param AvailabilityPerCustomer $availability
     */
    public function __construct(AvailabilityPerCustomer $availability)
    {
        $this->availability = $availability;
    }

    /**
     * @return AvailabilityPerCustomer
     */
    public function getAvailability()
    {
        return $this->availability;
    }
}
