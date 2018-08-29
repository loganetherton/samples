<?php
namespace ValuePad\Core\Appraiser\Notifications;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;

class ChangeCustomerFeesNotification extends AbstractAppraiserNotification
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @param Appraiser $appraiser
     * @param Customer $customer
     */
    public function __construct(Appraiser $appraiser, Customer $customer)
    {
        parent::__construct($appraiser);
        $this->customer = $customer;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
