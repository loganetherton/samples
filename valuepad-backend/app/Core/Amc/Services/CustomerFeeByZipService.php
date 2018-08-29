<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\CustomerFeeByZip;
use ValuePad\Core\Amc\Entities\FeeByZip;
use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeByZipService extends AbstractFeeByZipService
{
    use UseCustomerFeeByStateTrait;
    use UseCustomerFeeTrait;

    /**
     * @return string
     */
    protected function getFeeByZipClass()
    {
        return CustomerFeeByZip::class;
    }

    /**
     * @param CustomerFee $customerFee
     * @param FeeByZip $feeByZip
     * @param bool $flush When set to false, the unit of work won't be committed
     * @return CustomerFeeByZip
     */
    public function makeWithDefaultZipFee(CustomerFee $customerFee, FeeByZip $feeByZip, $flush = false)
    {
        $customerFeeByZip = new CustomerFeeByZip();
        $customerFeeByZip->setAmount($feeByZip->getAmount());
        $customerFeeByZip->setZip($feeByZip->getZip());
        $customerFeeByZip->setFee($customerFee);

        $this->entityManager->persist($customerFeeByZip);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $customerFeeByZip;
    }
}
