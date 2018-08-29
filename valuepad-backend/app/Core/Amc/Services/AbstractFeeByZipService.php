<?php
namespace ValuePad\Core\Amc\Services;
use ValuePad\Core\Amc\Entities\AbstractFeeByState;
use ValuePad\Core\Amc\Entities\AbstractFeeByZip;
use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Entities\Fee;
use ValuePad\Core\Amc\Enums\Scope;
use ValuePad\Core\Amc\Notifications\ChangeCustomerFeesNotification;
use ValuePad\Core\Amc\Persistables\FeeByZipPersistable;
use ValuePad\Core\Support\Synchronizer;
use ValuePad\Core\Amc\Validation\SyncFeesByZipValidator;
use ValuePad\Core\Assignee\Entities\CustomerFee;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\ZipService;
use ValuePad\Core\Support\Service\AbstractService;

abstract class AbstractFeeByZipService extends AbstractService
{
    /**
     * @param int $feeId
     * @param string $code
     * @return AbstractFeeByZip[]|object[]
     */
    public function getAllByStateCode($feeId, $code)
    {
        /**
         * @var ZipService $zipService
         */
        $zipService = $this->container->get(ZipService::class);

        $zips = $zipService->getAllInState($code);

        return $this->entityManager->getRepository($this->getFeeByZipClass())
            ->retrieveAll(['zip' => ['in', $zips], 'fee' => $feeId]);
    }

    /**
     * @param int $feeId
     * @param string $state
     * @param FeeByZipPersistable[] $persistables
     * @return AbstractFeeByZip[]
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

        (new SyncFeesByZipValidator($this->container, $state))
            ->validate(['data' => $persistables]);

        $result = $this->syncInStateInMemory($fee, $state, $persistables);

        $this->entityManager->flush();

        if ($fee instanceof CustomerFee){

            $notification = new ChangeCustomerFeesNotification($fee->getAssignee(), $fee->getCustomer(), new Scope(Scope::BY_ZIP));
            $notification->setState($state);

            $this->notify($notification);
        }

        return $result;
    }

    /**
     * @param Fee|CustomerFee $fee
     * @param State $state
     * @param FeeByZipPersistable[] $persistables
     * @return AbstractFeeByZip[]
     */
    private function syncInStateInMemory($fee, State $state, array $persistables)
    {
        $synchronizer = new Synchronizer();

        $synchronizer
            ->identify1(function(AbstractFeeByZip $feeByZip){
                return $feeByZip->getZip();
            })
            ->identify2(function(FeeByZipPersistable $persistable){
                return $persistable->getZip();
            })
            ->onRemove(function(AbstractFeeByZip $feeByZip){
                $this->entityManager->remove($feeByZip);
            })
            ->onCreate(function(FeeByZipPersistable $persistable) use ($fee) {
                return $this->createInMemory($fee, $persistable);
            })
            ->onUpdate(function(AbstractFeeByZip $feeByZip, FeeByZipPersistable $persistable){
                $this->updateInMemory($feeByZip, $persistable);
            });

        $feesByZip = $this->getAllByStateCode($fee->getId(), $state->getCode());

        return $synchronizer->synchronize($feesByZip, $persistables);
    }

    /**
     * @param Fee|CustomerFee $fee
     * @param FeeByZipPersistable $persistable
     * @return AbstractFeeByZip
     */
    private function createInMemory($fee, FeeByZipPersistable $persistable)
    {
        $class = $this->getFeeByZipClass();
        $feeByZip = new $class();
        $feeByZip->setFee($fee);

        $this->exchange($persistable, $feeByZip);

        $this->entityManager->persist($feeByZip);

        return $feeByZip;
    }

    /**
     * @param AbstractFeeByZip $feeByZip
     * @param FeeByZipPersistable $persistable
     */
    private function updateInMemory(AbstractFeeByZip $feeByZip, FeeByZipPersistable $persistable)
    {
        $this->exchange($persistable, $feeByZip);
    }

    /**
     * @param FeeByZipPersistable $persistable
     * @param AbstractFeeByZip $feeByZip
     */
    private function exchange(FeeByZipPersistable $persistable, AbstractFeeByZip $feeByZip)
    {
        $this->transfer($persistable, $feeByZip);
    }

    /**
     * @param string $feeByStateId
     */
    public function applyStateAmountToAllInState($feeByStateId)
    {
        /**
         * @var ZipService $zipService
         */
        $zipService = $this->container->get(ZipService::class);

        /**
         * @var AbstractFeeByState $feeByState
         */
        $feeByState = $this->entityManager->find($this->getFeeByStateClass(), $feeByStateId);

        $zips = $zipService->getAllInState($feeByState->getState()->getCode());

        $persistables = [];

        foreach ($zips as $zip){
            $persistable = new FeeByZipPersistable();
            $persistable->setAmount($feeByState->getAmount());
            $persistable->setZip($zip);
            $persistables[] = $persistable;
        }

        $this->syncInStateInMemory($feeByState->getFee(), $feeByState->getState(), $persistables);

        $this->entityManager->flush();

        if ($feeByState instanceof CustomerFeeByState){
            $fee = $feeByState->getFee();
            $notification = new ChangeCustomerFeesNotification($fee->getAssignee(), $fee->getCustomer(), new Scope(Scope::BY_ZIP));
            $notification->setState($feeByState->getState());
            $this->notify($notification);
        }
    }

    /**
     * @return string
     */
    abstract protected function getFeeByZipClass();

    /**
     * @return string
     */
    abstract protected function getFeeByStateClass();

    /**
     * @return string
     */
    abstract protected function getFeeClass();
}
