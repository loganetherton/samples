<?php
namespace ValuePad\Core\Assignee\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\CustomerFeeByCounty;
use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Entities\CustomerFeeByZip;
use ValuePad\Core\Amc\Enums\Scope;
use ValuePad\Core\Amc\Notifications\ChangeCustomerFeesNotification as AmcChangeCustomerFeesNotification;
use ValuePad\Core\Appraiser\Notifications\ChangeCustomerFeesNotification as AppraiserChangeCustomerFeesNotification;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Assignee\Entities\CustomerFee;
use ValuePad\Core\Assignee\Persistables\FeePersistable;
use ValuePad\Core\Assignee\Validation\CreateCustomerFeeValidator;
use ValuePad\Core\Assignee\Validation\UpdateFeeValidator;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

abstract class CustomerFeeService extends AbstractService
{
    use FeeCommonsTrait;

    /**
     * @param int $assigneeId
     * @param int $customerId
     * @return CustomerFee[]
     */
    public function getAll($assigneeId, $customerId)
    {
        return $this->entityManager->getRepository(CustomerFee::class)->findBy([
            'assignee' => $assigneeId,
            'customer' => $customerId
        ]);
    }

    /**
     * @param int $assigneeId
     * @param int $customerId
     * @param FeePersistable $persistable
     * @return CustomerFee
     */
    public function create($assigneeId, $customerId, FeePersistable $persistable)
    {
        /**
         * @var Appraiser|Amc $assignee
         */
        $assignee = $this->entityManager->getReference($this->getAssigneeClass(), $assigneeId);

        /**
         * @var Customer $customer
         */
        $customer = $this->entityManager->find(Customer::class, $customerId);

        $this->verifyChangeFeesPermissions($assignee, $customer);

        (new CreateCustomerFeeValidator(
            $this->container,
            $assignee,
            $customer,
            $this->getAssigneeServiceClass()
        ))->validate($persistable);

        $fee = $this->createInMemory($assignee, $customer, $persistable);

        $this->entityManager->persist($fee);

        $this->entityManager->flush();

        if ($assignee instanceof Amc){
            $this->notify(new AmcChangeCustomerFeesNotification($assignee, $customer, new Scope(Scope::NORMAL)));
        }

        if ($assignee instanceof Appraiser){
            $this->notify(new AppraiserChangeCustomerFeesNotification($assignee, $customer));
        }

        return $fee;
    }

    /**
     * @param int $assigneeId
     * @param int $customerId
     * @param FeePersistable[] $persistables
     * @return CustomerFee[]
     */
    public function createBulk($assigneeId, $customerId, array $persistables)
    {
        /**
         * @var Appraiser|Amc $assignee
         */
        $assignee = $this->entityManager->getReference($this->getAssigneeClass(), $assigneeId);

        /**
         * @var Customer $customer
         */
        $customer = $this->entityManager->find(Customer::class, $customerId);

        $this->verifyChangeFeesPermissions($assignee, $customer);

        $hash = $this->prepareJobTypeAmountHash($persistables);

        $this->verifyJobTypesUniqueness($hash, $persistables);
        $this->verifyFeeAmounts($hash);

        $fees = [];

        foreach ($persistables as $persistable) {
            (new CreateCustomerFeeValidator(
                $this->container,
                $assignee,
                $customer,
                $this->getAssigneeServiceClass()
            ))->validate($persistable);

            $fee = $this->createInMemory($assignee, $customer, $persistable);

            $this->entityManager->persist($fee);

            $fees[] = $fee;
        }

        $this->entityManager->flush();

        if ($assignee instanceof Amc){
            $this->notify(new AmcChangeCustomerFeesNotification($assignee, $customer, new Scope(Scope::NORMAL)));
        }

        if ($assignee instanceof Appraiser){
            $this->notify(new AppraiserChangeCustomerFeesNotification($assignee, $customer));
        }

        return $fees;
    }

    /**
     * @param Appraiser|Amc $assignee
     * @param Customer $customer
     * @param FeePersistable $persistable
     * @return CustomerFee
     */
    private function createInMemory($assignee, Customer $customer, FeePersistable $persistable)
    {
        $fee = new CustomerFee();

        $fee->setCustomer($customer);

        $fee->setAssignee($assignee);

        /**
         * @var JobType $jobType
         */
        $jobType = $this->entityManager->getReference(JobType::class, $persistable->getJobType());

        $fee->setJobType($jobType);

        $fee->setAmount($persistable->getAmount());

        return $fee;
    }

    /**
     * @param int $id
     * @param FeePersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($id, FeePersistable $persistable, UpdateOptions  $options = null)
    {
        if ($options === null){
            $options = new UpdateOptions();
        }

        (new UpdateFeeValidator())
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->validate($persistable);

        /**
         * @var CustomerFee $fee
         */
        $fee = $this->entityManager->find(CustomerFee::class, $id);

        $this->verifyChangeFeesPermissions($fee->getAssignee(), $fee->getCustomer());

        $fee->setAmount($persistable->getAmount());

        $this->entityManager->flush();

        $assignee = $fee->getAssignee();
        $customer = $fee->getCustomer();

        if ($assignee instanceof Amc){
            $this->notify(new AmcChangeCustomerFeesNotification($assignee, $customer, new Scope(Scope::NORMAL)));
        }

        if ($assignee instanceof Appraiser){
            $this->notify(new AppraiserChangeCustomerFeesNotification($assignee, $customer));
        }
    }

    /**
     * @param int $assigneeId
     * @param int $customerId
     * @param array $amounts - id => amount
     */
    public function updateBulkSharedAmongAssigneeAndCustomer($assigneeId, $customerId, array $amounts)
    {
        $this->verifyFeeAmounts($amounts);

        /**
         * @var Appraiser|Amc $assignee
         */
        $assignee = $this->entityManager->getReference($this->getAssigneeClass(), $assigneeId);

        /**
         * @var Customer $customer
         */
        $customer = $this->entityManager->getReference(Customer::class, $customerId);

        $this->verifyChangeFeesPermissions($assignee, $customer);

        /**
         * @var CustomerFee[] $fees
         */
        $fees = $this->entityManager->getRepository(CustomerFee::class)
            ->retrieveAll(['customer' => $customerId, 'assignee' => $assigneeId, 'id' => ['in', array_keys($amounts)]]);

        foreach ($fees as $fee){
            $fee->setAmount($amounts[$fee->getId()]);
        }

        $this->entityManager->flush();

        if ($assignee instanceof Amc){
            $this->notify(new AmcChangeCustomerFeesNotification($assignee, $customer, new Scope(Scope::NORMAL)));
        }

        if ($assignee instanceof Appraiser){
            $this->notify(new AppraiserChangeCustomerFeesNotification($assignee, $customer));
        }
    }

    /**
     * @param int $assigneeId
     * @param int $customerId
     * @param array $ids
     */
    public function deleteBulkSharedAmongAssigneeAndCustomer($assigneeId, $customerId, array $ids)
    {
        /**
         * @var Appraiser|Amc $assignee
         */
        $assignee = $this->entityManager->getReference($this->getAssigneeClass(), $assigneeId);

        /**
         * @var Customer $customer
         */
        $customer = $this->entityManager->getReference(Customer::class, $customerId);

        $this->verifyChangeFeesPermissions($assignee, $customer);

        $this->entityManager->getRepository(CustomerFee::class)->delete([
            'customer' => $customerId, 'assignee' => $assigneeId, 'id' => ['in', $ids]
        ]);

        if ($assignee instanceof Amc){
            $this->notify(new AmcChangeCustomerFeesNotification($assignee, $customer, new Scope(Scope::NORMAL)));
        }

        if ($assignee instanceof Appraiser){
            $this->notify(new AppraiserChangeCustomerFeesNotification($assignee, $customer));
        }
    }

    /**
     * @param Appraiser|Amc $assignee
     * @param Customer $customer
     */
    private function verifyChangeFeesPermissions($assignee, Customer $customer)
    {
        /**
         * @var AppraiserService|AmcService $assigneeService
         */
        $assigneeService = $this->container->get($this->getAssigneeServiceClass());

        if ($customer->getSettings()->getDisallowChangeJobTypeFees() === true
            && $assigneeService->hasAnyCustomerFee($assignee->getId(), $customer->getId())){
            throw new PresentableException('The assignee is not allowed change fees for the provided customer.');
        }
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        /**
         * @var CustomerFee $fee
         */
        $fee = $this->entityManager->getReference(CustomerFee::class, $id);

        $this->verifyChangeFeesPermissions($fee->getAssignee(), $fee->getCustomer());

        $this->entityManager->remove($fee);

        if ($fee->getAssignee() instanceof Amc) {
            $classes = [CustomerFeeByState::class, CustomerFeeByCounty::class, CustomerFeeByZip::class];

            foreach ($classes as $class) {
                $this->entityManager
                    ->getRepository($class)
                    ->delete(['fee' => $fee->getId()]);
            }
        }

        $this->entityManager->flush();

        $assignee = $fee->getAssignee();
        $customer = $fee->getCustomer();

        if ($assignee instanceof Amc){
            $this->notify(new AmcChangeCustomerFeesNotification($assignee, $customer, new Scope(Scope::NORMAL)));
        }

        if ($assignee instanceof Appraiser){
            $this->notify(new AppraiserChangeCustomerFeesNotification($assignee, $customer));
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->entityManager->getRepository(CustomerFee::class)->exists(['id' => $id]);
    }

    /**
     * Returns the string of the class to be used as the assignee
     *
     * @return string
     */
    abstract protected function getAssigneeClass();

    /**
     * Returns the string assignee service class
     *
     * @return string
     */
    abstract protected function getAssigneeServiceClass();
}
