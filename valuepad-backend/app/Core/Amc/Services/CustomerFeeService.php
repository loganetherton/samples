<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\CustomerFeeByCounty;
use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Entities\CustomerFeeByZip;
use ValuePad\Core\Amc\Entities\Fee;
use ValuePad\Core\Amc\Entities\FeeByCounty;
use ValuePad\Core\Amc\Entities\FeeByState;
use ValuePad\Core\Amc\Entities\FeeByZip;
use ValuePad\Core\Amc\Services\CustomerFeeByZipService;
use ValuePad\Core\Amc\Services\FeeService;
use ValuePad\Core\Assignee\Entities\CustomerFee;
use ValuePad\Core\Assignee\Services\CustomerFeeService as AbstractCustomerFeeService;
use ValuePad\Core\Location\Entities\Zip;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Services\JobTypeService;
use ValuePad\Core\Support\Synchronizer;

class CustomerFeeService extends AbstractCustomerFeeService
{
    protected function getAssigneeClass()
    {
        return Amc::class;
    }

    protected function getAssigneeServiceClass()
    {
        return AmcService::class;
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @return CustomerFee
     */
    public function getByJobTypeId($amcId, $customerId, $jobTypeId)
    {
        return $this->entityManager->getRepository(CustomerFee::class)
            ->findOneBy(['assignee' => $amcId, 'customer' => $customerId, 'jobType' => $jobTypeId]);
    }

    /**
     * @param int $feeId
     * @param string $code
     * @return bool
     */
    public function hasCustomerFeeByStateByStateCode($feeId, $code)
    {
        return $this->entityManager->getRepository(CustomerFeeByState::class)
            ->exists(['fee' => $feeId, 'state' => $code]);
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param string $zip
     * @return float
     */
    public function determineAmountByJobTypeIdAndZip($amcId, $customerId, $jobTypeId, $zip)
    {
        $fee = $this->getByJobTypeId($amcId, $customerId, $jobTypeId);

        if (!$fee){
            return null;
        }

        /**
         * @var CustomerFeeByZip $feeByZip
         */
        $feeByZip = $this->entityManager->getRepository(CustomerFeeByZip::class)->findOneBy([
            'fee' => $fee->getId(),
            'zip' => $zip
        ]);

        if ($feeByZip){
            return $feeByZip->getAmount();
        }

        /**
         * @var Zip $zip
         */
        $zip = $this->entityManager->getRepository(Zip::class)->findOneBy(['code' => $zip]);

        if (!$zip){
            return $fee->getAmount();
        }

        /**
         * @var CustomerFeeByCounty $feeByCounty
         */
        $feeByCounty = $this->entityManager->getRepository(CustomerFeeByCounty::class)->findOneBy([
            'fee' => $fee->getId(),
            'county' => $zip->getCounty()->getId()
        ]);

        if ($feeByCounty){
            return $feeByCounty->getAmount();
        }

        /**
         * @var CustomerFeeByState $feeByState
         */
        $feeByState = $this->entityManager->getRepository(CustomerFeeByState::class)->findOneBy([
            'fee' => $fee->getId(),
            'state' => $zip->getCounty()->getState()->getCode()
        ]);

        if ($feeByState){
            return $feeByState->getAmount();
        }

        return $fee->getAmount();
    }

    /**
     * @param int $amcId
     * @param int $customerId
     */
    public function syncWithDefaultLocationFees($amcId, $customerId)
    {
        $feeService = $this->container->get(FeeService::class);
        $customerJobTypeRepo = $this->entityManager->getRepository(JobType::class);
        $fees = $feeService->getAllEnabled($amcId);

        foreach ($fees as $fee) {
            $customerJobTypes = $customerJobTypeRepo->findBy([
                'customer' => $customerId,
                'local' => $fee->getJobType()->getId(),
                'isPayable' => true,
                'isHidden' => false
            ]);

            foreach ($customerJobTypes as $customerJobType) {
                $customerFee = $this->getByJobTypeId($amcId, $customerId, $customerJobType->getId());

                if (! $customerFee) {
                    $customerFee = new CustomerFee();
                    $customerFee->setAmount($fee->getAmount());
                    $customerFee->setJobType($customerJobType);
                    $customerFee->setCustomer($customerJobType->getCustomer());
                    $customerFee->setAssignee($fee->getAmc());

                    $this->entityManager->persist($customerFee);
                    $this->entityManager->flush();
                }

                $this->copyDefaultStateFees($fee, $customerFee);
                $this->copyDefaultCountyFees($fee, $customerFee);
                $this->copyDefaultZipFees($fee, $customerFee);
            }
        }
    }

    /**
     * @param Fee $fee
     * @param CustomerFee $customerFee
     */
    private function copyDefaultStateFees(Fee $fee, CustomerFee $customerFee)
    {
        $defaultStateFees = $this->entityManager
            ->getRepository(FeeByState::class)
            ->retrieveAll(['fee' => $fee->getId()]);

        $customerStateFees = $this->entityManager
            ->getRepository(CustomerFeeByState::class)
            ->retrieveAll(['fee' => $customerFee->getId()]);

        $synchronizer = new Synchronizer();
        $synchronizer
            ->identify1(function (CustomerFeeByState $customerFeeByState) {
                return $customerFeeByState->getState()->getCode();
            })
            ->identify2(function (FeeByState $feeByState) {
                return $feeByState->getState()->getCode();
            })
            ->onRemove(function () {
                // do nothing
            })
            ->onCreate(function (FeeByState $feeByState) use ($customerFee) {
                $this->makeStateFee($customerFee, $feeByState);
            })
            ->onUpdate(function (CustomerFeeByState $customerFeeByState, FeeByState $feeByState) {
                $this->updateAmount($customerFeeByState, $feeByState);
            });

        $synchronizer->synchronize($customerStateFees, $defaultStateFees);

        $this->entityManager->flush();
    }

    /**
     * @param Fee $fee
     * @param CustomerFee $customerFee
     */
    private function copyDefaultCountyFees(Fee $fee, CustomerFee $customerFee)
    {
        $defaultCountyFees = $this->entityManager
            ->getRepository(FeeByCounty::class)
            ->retrieveAll(['fee' => $fee->getId()]);

        $customerCountyFees = $this->entityManager
            ->getRepository(CustomerFeeByCounty::class)
            ->retrieveAll(['fee' => $customerFee->getId()]);

        $synchronizer = new Synchronizer();
        $synchronizer
            ->identify1(function (CustomerFeeByCounty $customerFeeByCounty) {
                return $customerFeeByCounty->getCounty()->getId();
            })
            ->identify2(function (FeeByCounty $feeByCounty) {
                return $feeByCounty->getCounty()->getId();
            })
            ->onRemove(function () {
                // do nothing
            })
            ->onCreate(function (FeeByCounty $feeByCounty) use ($customerFee) {
                $this->makeCountyFee($customerFee, $feeByCounty);
            })
            ->onUpdate(function (CustomerFeeByCounty $customerFeeByCounty, FeeByCounty $feeByCounty) {
                $this->updateAmount($customerFeeByCounty, $feeByCounty);
            });

        $synchronizer->synchronize($customerCountyFees, $defaultCountyFees);

        $this->entityManager->flush();
    }

    private function copyDefaultZipFees(Fee $fee, CustomerFee $customerFee)
    {
        $defaultZipFees = $this->entityManager
            ->getRepository(FeeByZip::class)
            ->retrieveAll(['fee' => $fee->getId()]);

        $customerZipFees = $this->entityManager
            ->getRepository(CustomerFeeByZip::class)
            ->retrieveAll(['fee' => $customerFee->getId()]);

        $synchronizer = new Synchronizer();
        $synchronizer
            ->identify1(function (CustomerFeeByZip $customerFeeByZip) {
                return $customerFeeByZip->getZip();
            })
            ->identify2(function (FeeByZip $feeByZip) {
                return $feeByZip->getZip();
            })
            ->onRemove(function () {
                // do nothing
            })
            ->onCreate(function (FeeByZip $feeByZip) use ($customerFee) {
                $this->makeZipFee($customerFee, $feeByZip);
            })
            ->onUpdate(function (CustomerFeeByZip $customerFeeByZip, $feeByZip) {
                $this->updateAmount($customerFeeByZip, $feeByZip);
            });

        $synchronizer->synchronize($customerZipFees, $defaultZipFees);

        $this->entityManager->flush();
    }

    /**
     * @param CustomerFeeByState|CustomerFeeByCounty|CustomerFeeByZip $customerLocationFee
     * @param FeeByState|FeeByCounty|FeeByZip $defaultLocationFee
     */
    private function updateAmount($customerLocationFee, $defaultLocationFee)
    {
        $customerLocationFee->setAmount($defaultLocationFee->getAmount());
    }

    /**
     * @param CustomerFee $customerFee
     * @param FeeByState $feeByState
     */
    private function makeStateFee(CustomerFee $customerFee, FeeByState $feeByState)
    {
        $customerFeeByStateService = $this->container->get(CustomerFeeByStateService::class);
        $customerFeeByStateService->makeWithDefaultStateFee($customerFee, $feeByState);
    }

    /**
     * @param CustomerFee $customerFee
     * @param FeeByCounty $feeByCounty
     */
    private function makeCountyFee(CustomerFee $customerFee, FeeByCounty $feeByCounty)
    {
        $customerFeeByCountyService = $this->container->get(CustomerFeeByCountyService::class);
        $customerFeeByCountyService->makeWithDefaultCountyFee($customerFee, $feeByCounty);
    }

    /**
     * @param CustomerFee $customerFee
     * @param FeeByZip $feeByZip
     */
    private function makeZipFee(CustomerFee $customerFee, FeeByZip $feeByZip)
    {
        $customerFeeByZipService = $this->container->get(CustomerFeeByZipService::class);
        $customerFeeByZipService->makeWithDefaultZipFee($customerFee, $feeByZip);
    }
}