<?php
namespace ValuePad\Core\Amc\Services;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\Fee;
use ValuePad\Core\Amc\Entities\Invoice;
use ValuePad\Core\Amc\Entities\License;
use ValuePad\Core\Amc\Entities\Settings;
use ValuePad\Core\Payment\Services\PaymentService;
use ValuePad\Core\Shared\Interfaces\TokenGeneratorInterface;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Core\Amc\Notifications\ApproveAmcNotification;
use ValuePad\Core\Amc\Notifications\CreateAmcNotification;
use ValuePad\Core\Amc\Notifications\DeclineAmcNotification;
use ValuePad\Core\Amc\Options\FetchAmcsOptions;
use ValuePad\Core\Amc\Persistables\AmcPersistable;
use ValuePad\Core\Amc\Validation\AmcValidator;
use ValuePad\Core\Assignee\Services\AssigneeService;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Criteria\Paginator;
use ValuePad\Core\User\Interfaces\PasswordEncryptorInterface;
use Exception;
use Log;

class AmcService extends AssigneeService
{
    /**
     * @param AmcPersistable $persistable
     * @return Amc
     */
    public function create(AmcPersistable $persistable)
    {
        (new AmcValidator($this->container))->validate($persistable);

        $amc = new Amc();

        $this->exchange($persistable, $amc);

        /**
         * @var TokenGeneratorInterface $generator
         */
        $generator = $this->container->get(TokenGeneratorInterface::class);

        $amc->setSecret1($generator->generate());
        $amc->setSecret2($generator->generate());

        $this->entityManager->persist($amc);

        $this->entityManager->flush();

        $settings = new Settings();
        $settings->setAmc($amc);

        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        $amc->setSettings($settings);

        $this->notify(new CreateAmcNotification($amc));

        return $amc;
    }

    /**
     * @param $id
     * @param AmcPersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($id, AmcPersistable $persistable, UpdateOptions $options = null)
    {
        if ($options === null) {
            $options = new UpdateOptions();
        }

        /**
         * @var Amc $amc
         */
        $amc = $this->entityManager->find(Amc::class, $id);

        (new AmcValidator($this->container))
            ->setCurrentAmc($amc)
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->validate($persistable, true);

        $this->exchange($persistable, $amc, $options->getPropertiesScheduledToClear());

        $this->entityManager->flush();

        /**
         * @var PaymentService $paymentService
         */
        $paymentService = $this->container->get(PaymentService::class);

        if ($persistable->getEmail()
            || $persistable->getCompanyName()
            || $persistable->getPhone()
            || $persistable->getFax()
            || $options->isPropertyScheduledToClear('fax')){

            try {
                $paymentService->refreshProfile($amc->getId());
            } catch (Exception $ex){
                Log::warning($ex);
            }
        }

        if ($status = $persistable->getStatus()) {
            if ($status->is(Status::APPROVED)) {
                $this->notify(new ApproveAmcNotification($amc));
            } elseif ($status->is(Status::DECLINED)) {
                $this->notify(new DeclineAmcNotification($amc));
            }
        }
    }

    /**
     * @param AmcPersistable $persistable
     * @param Amc $amc
     * @param array $nullable
     */
    private function exchange(AmcPersistable $persistable, Amc $amc, array $nullable = [])
    {
        $nullable = array_filter($nullable, function ($v) {
            return !in_array($v, ['status']);
        });

        $this->transfer($persistable, $amc, [
            'ignore' => [
                'state',
                'password'
            ],
            'nullable' => $nullable
        ]);

        if ($state = $persistable->getState()) {
            /**
             * @var State $state
             */
            $state = $this->entityManager->getReference(State::class, $state);
            $amc->setState($state);
        }

        if ($password = $persistable->getPassword()) {
            /**
             * @var PasswordEncryptorInterface $encryptor
             */
            $encryptor = $this->container->get(PasswordEncryptorInterface::class);

            $amc->setPassword($encryptor->encrypt($password));
        }
    }

    /**
     * @param FetchAmcsOptions $options
     * @return Amc[]
     */
    public function getAll(FetchAmcsOptions $options = null)
    {
        if ($options === null) {
            $options = new FetchAmcsOptions();
        }

        $builder = $this->entityManager->createQueryBuilder();

        $builder->from(Amc::class, 'a')->select('a');

        return (new Paginator())->apply($builder, $options->getPagination());
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        $builder = $this->entityManager->createQueryBuilder();

        return (int) $builder
            ->from(Amc::class, 'a')
            ->select($builder->expr()->count('a'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $id
     * @return Amc
     */
    public function getApproved($id)
    {
        return $this->entityManager->getRepository(Amc::class)
            ->findOneBy(['status' => Status::APPROVED, 'id' => $id]);
    }

    /**
     * @param int $id
     * @return Amc
     */
    public function get($id)
    {
        return $this->entityManager->getRepository(Amc::class)->findOneBy(['id' => $id]);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function existsApproved($id)
    {
        return $this->entityManager->getRepository(Amc::class)
            ->exists(['status' => Status::APPROVED, 'id' => $id]);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->entityManager->getRepository(Amc::class)
            ->exists(['id' => $id]);
    }

    /**
     * @param int $customerId
     * @param FetchAmcsOptions $options
     * @return Amc[]
     */
    public function getAllByCustomerId($customerId, FetchAmcsOptions $options = null)
    {
        if ($options === null) {
            $options = new FetchAmcsOptions();
        }


        $builder = $this->entityManager->createQueryBuilder();

        $builder
            ->from(Amc::class, 'a')
            ->select('a')
            ->where($builder->expr()->isMemberOf(':customer', 'a.customers'))
            ->setParameter('customer', $customerId);

        return (new Paginator())->apply($builder, $options->getPagination());
    }

    /**
     * @param int $customerId
     * @return int
     */
    public function getTotalByCustomerId($customerId)
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder
            ->select($builder->expr()->countDistinct('a'))
            ->from(Amc::class, 'a')
            ->where($builder->expr()->isMemberOf(':customer', 'a.customers'))
            ->setParameter('customer', $customerId);

        return (int) $builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $amcId
     * @param int $licenseId
     * @return bool
     */
    public function hasLicense($amcId, $licenseId)
    {
        return $this->entityManager->getRepository(License::class)
            ->exists(['amc' => $amcId, 'id' => $licenseId]);
    }

    /**
     * @param int $amcId
     * @param string $state
     */
    public function hasLicenseInState($amcId, $state)
    {
        return $this->entityManager
            ->getRepository(License::class)
            ->exists(['amc' => $amcId, 'state' => $state]);
    }

    /**
     * @param int $amcId
     * @param int $jobTypeId
     * @return bool
     */
    public function hasEnabledFeeByJobTypeId($amcId, $jobTypeId)
    {
        return $this->entityManager->getRepository(Fee::class)
            ->exists(['amc' => $amcId, 'jobType' => $jobTypeId, 'isEnabled' => true]);
    }

    /**
     * @param int $amcId
     * @param int $invoiceId
     * @return bool
     */
    public function hasInvoice($amcId, $invoiceId)
    {
        return $this->entityManager->getRepository(Invoice::class)
            ->exists(['amc' => $amcId, 'id' => $invoiceId]);
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @return bool
     */
    public function isRelatedWithCustomer($amcId, $customerId)
    {
        return $this->entityManager
            ->getRepository(Amc::class)
            ->exists(['customers' => ['HAVE MEMBER', $customerId], 'id' => $amcId]);
    }
}
