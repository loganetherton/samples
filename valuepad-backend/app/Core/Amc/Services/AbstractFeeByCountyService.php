<?php
namespace ValuePad\Core\Amc\Services;
use ValuePad\Core\Amc\Entities\AbstractFeeByCounty;
use ValuePad\Core\Amc\Entities\AbstractFeeByState;
use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Entities\Fee;
use ValuePad\Core\Amc\Enums\Scope;
use ValuePad\Core\Amc\Notifications\ChangeCustomerFeesNotification;
use ValuePad\Core\Amc\Persistables\FeeByCountyPersistable;
use ValuePad\Core\Support\Synchronizer;
use ValuePad\Core\Amc\Validation\SyncFeesByCountyValidator;
use ValuePad\Core\Assignee\Entities\CustomerFee;
use ValuePad\Core\Location\Entities\County;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\CountyService;
use ValuePad\Core\Support\Service\AbstractService;

abstract class AbstractFeeByCountyService extends AbstractService
{
    /**
     * @param int $feeId
     * @param string $code
     * @return AbstractFeeByCounty[]|object[]
     */
    public function getAllByStateCode($feeId, $code)
    {
        $builder = $this->entityManager->createQueryBuilder();

        return $builder
            ->from($this->getFeeByCountyClass(), 'f')
            ->select('f')
            ->leftJoin('f.county', 'c')
            ->where($builder->expr()->eq('c.state', ':state'))
            ->andWhere($builder->expr()->eq('f.fee', ':fee'))
            ->setParameter('state', $code)
            ->setParameter('fee', $feeId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $feeId
     * @param string $state
     * @param FeeByCountyPersistable[] $persistables
     * @return AbstractFeeByCounty[]
     */
    public function syncInState($feeId, $state, array $persistables)
    {
        /**
         * @var Fee|CustomerFee $fee
         */
        $fee = $this->entityManager->getReference($this->getFeeClass(), $feeId);

        /**
         * @var State $state
         */
        $state = $this->entityManager->find(State::class, $state);

        (new SyncFeesByCountyValidator($this->container, $state))
            ->validate(['data' => $persistables]);

        $result = $this->syncInStateInMemory($fee, $state, $persistables);

        $this->entityManager->flush();

        if ($fee instanceof CustomerFee){
            $notification = new ChangeCustomerFeesNotification($fee->getAssignee(), $fee->getCustomer(), new Scope(Scope::BY_COUNTY));

            $notification->setState($state);

            $this->notify($notification);
        }

        return $result;
    }

    /**
     * @param Fee|CustomerFee $fee
     * @param State $state
     * @param FeeByCountyPersistable[] $persistables
     * @return AbstractFeeByCounty[]
     */
    private function syncInStateInMemory($fee, State $state, array $persistables)
    {
        $synchronizer = new Synchronizer();

        $synchronizer
            ->identify1(function(AbstractFeeByCounty $feeByCounty){
                return $feeByCounty->getCounty()->getId();
            })
            ->identify2(function(FeeByCountyPersistable $persistable){
                return $persistable->getCounty();
            })
            ->onRemove(function(AbstractFeeByCounty $feeByCounty){
                $this->entityManager->remove($feeByCounty);
            })
            ->onCreate(function(FeeByCountyPersistable $persistable) use ($fee) {
                return $this->createInMemory($fee, $persistable);
            })
            ->onUpdate(function(AbstractFeeByCounty $feeByCounty, FeeByCountyPersistable $persistable){
                $this->updateInMemory($feeByCounty, $persistable);
            });

        $feesByCounty = $this->getAllByStateCode($fee->getId(), $state->getCode());

        return $synchronizer->synchronize($feesByCounty, $persistables);
    }

    /**
     * @param Fee $fee
     * @param FeeByCountyPersistable $persistable
     * @return AbstractFeeByCounty
     */
    private function createInMemory($fee, FeeByCountyPersistable $persistable)
    {
        $class = $this->getFeeByCountyClass();
        $feeByCounty = new $class();
        $feeByCounty->setFee($fee);

        $this->exchange($persistable, $feeByCounty);

        $this->entityManager->persist($feeByCounty);

        return $feeByCounty;
    }

    /**
     * @param AbstractFeeByCounty $feeByCounty
     * @param FeeByCountyPersistable $persistable
     */
    private function updateInMemory(AbstractFeeByCounty $feeByCounty, FeeByCountyPersistable $persistable)
    {
        $this->exchange($persistable, $feeByCounty);
    }

    /**
     * @param FeeByCountyPersistable $persistable
     * @param AbstractFeeByCounty $feeByCounty
     */
    private function exchange(FeeByCountyPersistable $persistable, AbstractFeeByCounty $feeByCounty)
    {
        $this->transfer($persistable, $feeByCounty, [
            'ignore' => [
                'county'
            ]
        ]);

        if ($persistable->getCounty()){
            /**
             * @var County $county
             */
            $county = $this->entityManager->getReference(County::class, $persistable->getCounty());
            $feeByCounty->setCounty($county);
        }
    }

    /**
     * @param int $feeByStateId
     */
    public function applyStateAmountToAllInState($feeByStateId)
    {
        /**
         * @var CountyService $countyService
         */
        $countyService = $this->container->get(CountyService::class);

        /**
         * @var AbstractFeeByState $feeByState
         */
        $feeByState = $this->entityManager->find($this->getFeeByStateClass(), $feeByStateId);

        $counties = $countyService->getAllInState($feeByState->getState()->getCode());

        $persistables = [];

        foreach ($counties as $county){
            $persistable = new FeeByCountyPersistable();
            $persistable->setAmount($feeByState->getAmount());
            $persistable->setCounty($county->getId());
            $persistables[] = $persistable;
        }

        $this->syncInStateInMemory($feeByState->getFee(), $feeByState->getState(), $persistables);

        $this->entityManager->flush();

        if ($feeByState instanceof CustomerFeeByState){
            $fee = $feeByState->getFee();
            $notification = new ChangeCustomerFeesNotification($fee->getAssignee(), $fee->getCustomer(), new Scope(Scope::BY_COUNTY));

            $notification->setState($feeByState->getState());

            $this->notify($notification);
        }
    }

    /**
     * @return string
     */
    abstract protected function getFeeByCountyClass();

    /**
     * @return string
     */
    abstract protected function getFeeByStateClass();

    /**
     * @return string
     */
    abstract protected function getFeeClass();
}
