<?php
namespace ValuePad\Core\Amc\Services;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use ValuePad\Core\Amc\Entities\AbstractFeeByState;
use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Entities\Fee;
use ValuePad\Core\Amc\Enums\Scope;
use ValuePad\Core\Amc\Notifications\ChangeCustomerFeesNotification;
use ValuePad\Core\Amc\Persistables\FeeByStatePersistable;
use ValuePad\Core\Support\Synchronizer;
use ValuePad\Core\Amc\Validation\SyncFeesByStateValidator;
use ValuePad\Core\Assignee\Entities\CustomerFee;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

abstract class AbstractFeeByStateService extends AbstractService
{
    /**
     * @param int $feeId
     * @param string $code
     * @return AbstractFeeByState
     */
    public function getByStateCode($feeId, $code)
    {
        return $this->entityManager->getRepository($this->getFeeByStateClass())
            ->findOneBy(['fee' => $feeId, 'state' => $code]);
    }

    /**
     * @param int $feeId
     * @return AbstractFeeByState[]
     */
    public function getAll($feeId)
    {
        return $this->entityManager->getRepository($this->getFeeByStateClass())->findBy(['fee' => $feeId]);
    }

    /**
     * @param int $feeId
     * @param FeeByStatePersistable[] $persistables
     * @return AbstractFeeByState[]
     */
    public function sync($feeId, array $persistables)
    {
        /**
         * @var StateService $stateService
         */
        $stateService = $this->container->get(StateService::class);

        (new SyncFeesByStateValidator($stateService))->validate(['data' => $persistables]);


        /**
         * @var Fee|CustomerFee $fee
         */
        $fee = $this->entityManager->getReference($this->getFeeClass(), $feeId);


        $synchronizer = new Synchronizer();

        $synchronizer
            ->identify1(function(AbstractFeeByState $feeByState){
                return $feeByState->getState()->getCode();
            })
            ->identify2(function(FeeByStatePersistable $persistable){
                return $persistable->getState();
            })
            ->onRemove(function(AbstractFeeByState $feeByState){
                $this->entityManager->remove($feeByState);
            })
            ->onCreate(function(FeeByStatePersistable $persistable) use ($fee) {
                return $this->createInMemory($fee, $persistable);
            })
            ->onUpdate(function(AbstractFeeByState $feeByState, FeeByStatePersistable $persistable){
                $this->updateInMemory($feeByState, $persistable);
            });


        $feesByState = $this->entityManager->getRepository($this->getFeeByStateClass())
            ->findBy(['fee' => $feeId]);

        $result = $synchronizer->synchronize($feesByState, $persistables);

        $this->entityManager->flush();

        if ($fee instanceof CustomerFee){
            $notification = new ChangeCustomerFeesNotification($fee->getAssignee(), $fee->getCustomer(), new Scope(Scope::BY_STATE));
            $notification->setJobType($fee->getJobType());
            $this->notify($notification);
        }

        return $result;
    }

    /**
     * @param Fee|CustomerFee $fee
     * @param FeeByStatePersistable $persistable
     * @return AbstractFeeByState
     */
    private function createInMemory($fee, FeeByStatePersistable $persistable)
    {
        $class = $this->getFeeByStateClass();
        $feeByState = new $class();
        $feeByState->setFee($fee);

        $this->exchange($persistable, $feeByState);

        $this->entityManager->persist($feeByState);

        return $feeByState;
    }

    /**
     * @param AbstractFeeByState $feeByState
     * @param FeeByStatePersistable $persistable
     * @param array $nullable
     */
    private function updateInMemory(AbstractFeeByState $feeByState, FeeByStatePersistable $persistable, array $nullable = [])
    {
        $this->exchange($persistable, $feeByState, $nullable);
    }

    /**
     * @param FeeByStatePersistable $persistable
     * @param AbstractFeeByState $feeByState
     * @param array $nullable
     */
    private function exchange(FeeByStatePersistable $persistable, AbstractFeeByState $feeByState, array $nullable = [])
    {
        $this->transfer($persistable, $feeByState, [
            'ignore' => [
                'state'
            ],
            'nullable' => $nullable
        ]);

        if ($persistable->getState()){
            /**
             * @var State $state
             */
            $state = $this->entityManager->getReference(State::class, $persistable->getState());

            $feeByState->setState($state);
        }
    }

    /**
     * @param int $feeByStateId
     * @param FeeByStatePersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($feeByStateId, FeeByStatePersistable $persistable, UpdateOptions $options = null)
    {
        if ($options === null){
            $options = new UpdateOptions();
        }

        /**
         * @var AbstractFeeByState $feeByState
         */
        $feeByState = $this->entityManager->find($this->getFeeByStateClass(), $feeByStateId);

        $this->getValidator()
            ->setCurrentFee($feeByState->getFee())
            ->setCurrentFeeByState($feeByState)
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->validate($persistable, true);

        $this->updateInMemory($feeByState, $persistable, $options->getPropertiesScheduledToClear());

        $this->entityManager->flush();

        if ($feeByState instanceof CustomerFeeByState){
            $fee = $feeByState->getFee();
            $notification = new ChangeCustomerFeesNotification($fee->getAssignee(), $fee->getCustomer(), new Scope(Scope::BY_STATE));
            $notification->setJobType($fee->getJobType());
            $this->notify($notification);
        }
    }

    /**
     * @return string
     */
    abstract protected function getFeeByStateClass();

    /**
     * @return string
     */
    abstract protected function getFeeClass();

    /**
     * @return AbstractThrowableValidator
     */
    abstract protected function getValidator();
}
