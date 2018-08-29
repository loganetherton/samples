<?php
namespace ValuePad\Core\Company\Services;

use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Criteria\StaffFilterResolver;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Invitation;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Company\Notifications\CreateStaffNotification;
use ValuePad\Core\Company\Options\CreateManagerOptions;
use ValuePad\Core\Company\Options\FetchStaffOptions;
use ValuePad\Core\Company\Persistables\ManagerAsStaffPersistable;
use ValuePad\Core\Company\Persistables\StaffPersistable;
use ValuePad\Core\Company\Validation\ManagerAsStaffValidator;
use ValuePad\Core\Company\Validation\StaffValidator;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Criteria\Filter;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Entities\User;

class StaffService extends AbstractService
{
    /**
     * @param int $companyId
     * @param ManagerAsStaffPersistable $persistable
     * @return Staff
     */
    public function createManager($companyId, ManagerAsStaffPersistable $persistable)
    {
        /**
         * @var Company $company
         */
        $company = $this->entityManager->find(Company::class, $companyId);

        (new ManagerAsStaffValidator($this->container, $company))->validate($persistable);

        /**
         * @var ManagerService $managerService
         */
        $managerService = $this->container->get(ManagerService::class);

        $manager = $managerService->create($persistable->getUser(), (new CreateManagerOptions())->setTrusted(true));

        $staff = new Staff();

        $manager->setStaff($staff);
        $staff->setUser($manager);
        $staff->setCompany($company);

        $this->exchange($persistable, $staff);

        $this->entityManager->persist($staff);
        $this->entityManager->flush();

        $this->notify(new CreateStaffNotification($staff, [
            CreateStaffNotification::EXTRA_PASSWORD => $persistable->getUser()->getPassword()
        ]));

        return $staff;
    }

    /**
     * @param int $staffId
     * @param StaffPersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($staffId, StaffPersistable $persistable, UpdateOptions $options = null)
    {
        if ($options === null){
            $options = new UpdateOptions();
        }

        $nullable = $options->getPropertiesScheduledToClear();

        $nullable = array_filter($nullable, function($value){
            return !in_array($value, ['isManager', 'isAdmin', 'isRfpManager']);
        });

        /**
         * @var Staff $staff
         */
        $staff = $this->entityManager->find(Staff::class, $staffId);

        (new StaffValidator($this->container, $staff->getCompany()))
            ->setForcedProperties($nullable)
            ->validate($persistable, true);

        $this->exchange($persistable, $staff, $nullable);

        $this->entityManager->flush();
    }

    /**
     * @param StaffPersistable $persistable
     * @param Staff $staff
     * @param array $nullable
     */
    private function exchange(StaffPersistable $persistable, Staff $staff, array $nullable = [])
    {
        $this->transfer($persistable, $staff, [
            'ignore' => ['branch', 'user'],
            'nullable' => $nullable
        ]);

        if ($branch = $persistable->getBranch()){
            $branch = $this->entityManager->getReference(Branch::class, $branch);
            $staff->setBranch($branch);
        }

        if ($staff->getUser() instanceof Manager){
            $staff->setManager(true);
        }
    }

    /**
     * @param Branch $branch
     * @param User $user
     * @return Staff
     */
    public function makeAdmin(Branch $branch, User $user)
    {
        $staff = new Staff();

        $staff->setCompany($branch->getCompany());
        $staff->setBranch($branch);
        $staff->setUser($user);
        $staff->setAdmin(true);

        $this->entityManager->persist($staff);
        $this->entityManager->flush();

        return $staff;
    }

    /**
     * @param Invitation $invitation
     * @return Staff
     */
    public function makeStaffByInvitation(Invitation $invitation)
    {
        /**
         * @var Branch
         */
        $branch = $invitation->getBranch();

       /**
         * @var Appraiser
         */
        $user = $invitation->getAscAppraiser()->getAppraiser();

        $staff = new Staff();

        $staff->setCompany($branch->getCompany());
        $staff->setEmail($invitation->getEmail());
        $staff->setPhone($invitation->getPhone());
        $staff->setBranch($branch);
        $staff->setUser($user);

        $this->entityManager->persist($staff);
        $this->entityManager->flush();

        return $staff;
    }

    /**
     * @param int $staffId
     */
    public function delete($staffId)
    {
        /**
         * @var Staff $staff
         */
        $staff = $this->entityManager->find(Staff::class, $staffId);
        $user = $staff->getUser();

        $isManager = $user instanceof Manager;

        $this->entityManager->remove($staff);
        $this->entityManager->flush();

        if ($isManager){
            /**
             * @var ManagerService $managerService
             */
            $managerService = $this->container->get(ManagerService::class);
            $managerService->delete($user->getId());
        }
    }

    /**
     * @param int $companyId
     * @return Staff[]
     */
    public function getAllByCompanyId($companyId)
    {
        return $this->entityManager
            ->getRepository(Staff::class)
            ->findBy(['company' => $companyId]);
    }

    /**
     * @param int $branchId
     * @param FetchStaffOptions $options
     * @return Staff[]
     */
    public function getAllByBranchId($branchId, FetchStaffOptions $options = null)
    {
        if ($options === null){
            $options = new FetchStaffOptions();
        }

        $builder = $this->entityManager->createQueryBuilder();

        $builder->select('s')->from(Staff::class, 's');
        $builder
            ->andWhere($builder->expr()->eq('s.branch', ':branch'))
            ->setParameter('branch', $branchId);

        (new Filter())->apply($builder, $options->getCriteria(), new StaffFilterResolver());

        return $builder->getQuery()->getResult();
    }

    /**
     * @param int $staffId
     * @return Staff
     */
    public function get($staffId)
    {
        return $this->entityManager->find(Staff::class, $staffId);
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function isAdmin($userId)
    {
        return $this->entityManager
            ->getRepository(Staff::class)
            ->exists(['user' => $userId, 'isAdmin' => true]);
    }

    /**
     * @param int $companyId
     * @param int $userId
     * @return Staff
     */
    public function getByCompanyAndUserIds($companyId, $userId)
    {
        return $this->entityManager->getRepository(Staff::class)
            ->findOneBy(['company' => $companyId, 'user' => $userId]);
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function isBoss($userId)
    {
        $builder = $this->entityManager->createQueryBuilder();

        return $builder->select($builder->expr()->countDistinct('s'))->from(Staff::class, 's')
            ->andWhere($builder->expr()->eq('s.user', ':user'))
            ->setParameter('user', $userId)
            ->andWhere('(s.isAdmin=:isAdmin OR s.isManager=:isManager)')
            ->setParameter('isAdmin', true)
            ->setParameter('isManager', true)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    /**
     * @param int $managerId
     * @param int $appraiserId
     * @return bool
     */
    public function isManagerFor($managerId, $appraiserId)
    {
        /**
         * @var Staff $managerStaff
         */
        $managerStaff = $this->entityManager->getRepository(Staff::class)
            ->findOneBy(['user' => $managerId, 'isManager' => true]);

        if ($managerStaff === null){
            return false;
        }

        return $this->entityManager->getRepository(Staff::class)->exists([
            'user' => $appraiserId, 'company' => $managerStaff->getCompany()->getId()
        ]);
    }
}
