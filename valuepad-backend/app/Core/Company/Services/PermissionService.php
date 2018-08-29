<?php
namespace ValuePad\Core\Company\Services;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Permission;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Support\Service\AbstractService;
use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Support\Synchronizer;
use ValuePad\Core\User\Entities\User;

class PermissionService extends AbstractService
{
    /**
     * @param int $managerStaffId
     * @return Staff[]
     */
    public function getAllAppraiserStaff($managerStaffId)
    {
        $builder = $this->entityManager->createQueryBuilder();

        $permissions = $builder
            ->select('p', 'a')
            ->from(Permission::class, 'p')
            ->join('p.appraiser', 'a')
            ->andWhere($builder->expr()->eq('p.manager', ':manager'))
            ->setParameter(':manager', $managerStaffId)
            ->getQuery()
            ->getResult();

        return array_map(function(Permission $permission){
            return $permission->getAppraiser();
        }, $permissions);
    }

    /**
     * @param int $managerStaffId
     * @param array $appraiserStaffIds
     */
    public function replaceAllAppraiserStaff($managerStaffId, array $appraiserStaffIds)
    {
        $appraiserStaffIds = array_filter(array_unique($appraiserStaffIds), function($id) use ($managerStaffId){
            return $id != $managerStaffId;
        });

        /**
         * @var Staff $managerStaff
         */
        $managerStaff = $this->entityManager->find(Staff::class, $managerStaffId);

        if (!$managerStaff->isManager()){
            throw new PresentableException('The "'.$managerStaffId.'" staff must be a manager');
        }

        $builder = $this->entityManager->createQueryBuilder();

        $appraiserStaff = $builder
            ->select('s')
            ->from(Staff::class, 's')
            ->join('s.user', 'u')

            ->andWhere($builder->expr()->in('s.id', ':appraiserStaff'))
            ->setParameter('appraiserStaff', $appraiserStaffIds)

            ->andWhere($builder->expr()->isInstanceOf('u', Appraiser::class))

            ->andWhere($builder->expr()->in('s.company', ':company'))
            ->setParameter('company', $managerStaff->getCompany()->getId())

            ->getQuery()
            ->getResult();

        if (count($appraiserStaffIds) !== count($appraiserStaff)){
            throw new PresentableException('One of the provided staff in the collection is not an appraiser in the same company with the provided manager');
        }

        /**
         * @var Permission[] $data
         */
        $permissions = $this->entityManager
            ->getRepository(Permission::class)
            ->findBy(['manager' => $managerStaffId]);

        (new Synchronizer())
            ->identify1(function(Permission $permission){
                return $permission->getAppraiser()->getId();
            })
            ->identify2(function(Staff $staff){
                return $staff->getId();
            })
            ->onCreate(function(Staff $appraiserStaff) use ($managerStaff){

                $permission = new Permission();
                $permission->setManager($managerStaff);
                $permission->setAppraiser($appraiserStaff);

                $this->entityManager->persist($permission);

                return $permission;
            })
            ->onRemove(function(Permission $permission){
                $this->entityManager->remove($permission);
            })
            ->onUpdate(function(Permission $permission, Staff $appraiserStaff){
                //
            })
            ->synchronize($permissions, $appraiserStaff);

        $this->entityManager->flush();
    }

    /**
     * @param int $appraiserId
     * @return User[]
     */
    public function getAllManagersByAppraiserId($appraiserId)
    {
        $builder = $this->entityManager->createQueryBuilder();

        /**
         * @var Permission[] $permissions
         */
        $permissions = $builder
            ->select('p', 'm')
            ->from(Permission::class, 'p')
            ->join('p.manager', 'm')
            ->join('p.appraiser', 'a')
            ->andWhere($builder->expr()->eq('a.user', ':appraiser'))
            ->andWhere($builder->expr()->eq('m.isManager', ':isManager'))
            ->setParameters([
                'appraiser' => $appraiserId,
                'isManager' => true
            ])
            ->getQuery()
            ->getResult();

        $managers = [];

        foreach ($permissions as $permission){
            $manager = $permission->getManager()->getUser();
            $managers[$manager->getId()] = $manager;
        }

        return array_values($managers);
    }

    /**
     * @param int $managerId
     * @return Appraiser[]
     */
    public function getAllAppraisersByManagerId($managerId)
    {
        $builder = $this->entityManager->createQueryBuilder();

        /**
         * @var Permission[] $permissions
         */
        $permissions = $builder
            ->select('p', 'a')
            ->from(Permission::class, 'p')
            ->join('p.manager', 'm')
            ->join('p.appraiser', 'a')
            ->andWhere($builder->expr()->eq('m.user', ':manager'))
            ->andWhere($builder->expr()->eq('m.isManager', ':isManager'))
            ->setParameters([
                'manager' => $managerId,
                'isManager' => true
            ])
            ->getQuery()
            ->getResult();

        $appraisers = [];

        foreach ($permissions as $permission){
            $appraiser = $permission->getAppraiser()->getUser();
            $appraisers[$appraiser->getId()] = $appraiser;
        }

        return array_values($appraisers);
    }

    /**
     * @param int $managerId
     * @return Company[]
     */
    public function getAllCompaniesByManagerId($managerId)
    {
        $builder = $this->entityManager->createQueryBuilder();

        /**
         * @var Permission[] $permissions
         */
        $permissions = $builder
            ->select('p', 'm', 'c')
            ->from(Permission::class, 'p')
            ->join('p.manager', 'm')
            ->join('m.company', 'c')
            ->andWhere($builder->expr()->eq('m.user', ':manager'))
            ->andWhere($builder->expr()->eq('m.isManager', ':isManager'))
            ->setParameters([
                'manager' => $managerId,
                'isManager' => true
            ])
            ->getQuery()
            ->getResult();

        $companies = [];

        foreach ($permissions as $permission){
            $company = $permission->getManager()->getCompany();
            $companies[$company->getId()] = $company;
        }

        return array_values($companies);
    }
}
