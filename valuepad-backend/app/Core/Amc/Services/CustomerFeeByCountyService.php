<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\CustomerFeeByCounty;
use ValuePad\Core\Amc\Entities\FeeByCounty;
use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeByCountyService extends AbstractFeeByCountyService
{
    use UseCustomerFeeByStateTrait;
    use UseCustomerFeeTrait;

    /**
     * @return string
     */
    protected function getFeeByCountyClass()
    {
        return CustomerFeeByCounty::class;
    }

    /**
     * @param CustomerFee $customerFee
     * @param FeeByCounty $feeByCounty
     * @param bool $flush When set to false, the unit of work won't be committed
     * @return CustomerFeeByCounty
     */
    public function makeWithDefaultCountyFee(CustomerFee $customerFee, FeeByCounty $feeByCounty, $flush = false)
    {
        $customerFeeByCounty = new CustomerFeeByCounty();
        $customerFeeByCounty->setAmount($feeByCounty->getAmount());
        $customerFeeByCounty->setCounty($feeByCounty->getCounty());
        $customerFeeByCounty->setFee($customerFee);

        $this->entityManager->persist($customerFeeByCounty);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $customerFeeByCounty;
    }
}
