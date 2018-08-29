<?php
namespace ValuePad\Core\Company\Services;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Fee;
use ValuePad\Core\Company\Persistables\FeePersistable;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Validation\CreateFeeValidator;
use ValuePad\Core\Company\Validation\SyncFeesValidator;
use ValuePad\Core\JobType\Entities\JobType;
use ValuePad\Core\JobType\Services\JobTypeService;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\Support\Synchronizer;

class FeeService extends AbstractService
{
    /**
     * @param int $companyId
     * @return Fee[]
     */
    public function getAll($companyId)
    {
        return $this->entityManager->getRepository(Fee::class)
            ->findBy(['company' => $companyId]);
    }

    /**
     * @param int $companyId
     * @param FeePersistable $persistable
     * @return Fee
     */
    public function create($companyId, FeePersistable $persistable)
    {
        $company = $this->entityManager->getReference(Company::class, $companyId);

        (new CreateFeeValidator(
            $this->container->get(JobTypeService::class),
            $this->container->get(CompanyService::class),
            $company
        ))->validate($persistable);

        $fee = new Fee();

        $fee->setJobType($this->entityManager->getReference(JobType::class, $persistable->getJobType()));
        $fee->setCompany($company);
        $fee->setAmount($persistable->getAmount());

        $this->entityManager->persist($fee);
        $this->entityManager->flush();

        return $fee;
    }

    /**
     * @param int $companyId
     * @param FeePersistable[] $persistables
     */
    public function sync($companyId, array $persistables)
    {
        /**
         * @var JobTypeService $jobTypeService
         */
        $jobTypeService = $this->container->get(JobTypeService::class);

        (new SyncFeesValidator($jobTypeService))->validate(['data' => $persistables]);

        $fees = $this->entityManager->getRepository(Fee::class)
            ->findBy(['company' => $companyId]);

        /**
         * @var Company $company
         */
        $company = $this->entityManager->getReference(Company::class, $companyId);

        (new Synchronizer())
            ->identify1(function(Fee $fee){
                return $fee->getJobType()->getId();
            })
            ->identify2(function(FeePersistable $persistable){
                return $persistable->getJobType();
            })
            ->onCreate(function(FeePersistable $persistable) use ($company){
                $fee = new Fee();

                $fee->setAmount($persistable->getAmount());
                $fee->setCompany($company);

                /**
                 * @var JobType $jobType
                 */
                $jobType = $this->entityManager->getReference(JobType::class, $persistable->getJobType());

                $fee->setJobType($jobType);

                $this->entityManager->persist($fee);

                return $fee;
            })
            ->onUpdate(function(Fee $fee, FeePersistable $persistable){
                $fee->setAmount($persistable->getAmount());
            })
            ->onRemove(function(Fee $fee){
                $this->entityManager->remove($fee);
            })
            ->synchronize($fees, $persistables);

        $this->entityManager->flush();
    }
}
