<?php
namespace ValuePad\Core\Company\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Appraiser\Entities\Eo;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Persistables\BranchPersistable;
use ValuePad\Core\Company\Validation\BranchValidator;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

class BranchService extends AbstractService
{
    /**
     * @param Company $company
     * @return Branch
     */
    public function makeDefault(Company $company)
    {
        $branch = new Branch();
        $branch->setName('Default Branch');
        $branch->setDefault(true);
        $branch->setCompany($company);
        $branch->setState($company->getState());

        $this->transfer($company, $branch, [
            'ignore' => [
                'id',
                'name',
                'type',
                'otherType',
                'w9',
                'firstName',
                'lastName',
                'email',
                'phone',
                'fax',
                'ach',
                'creator',
                'branches',
                'eo',
                'state',
            ]
        ]);

        if ($company->getEo()) {
            $eo = new Eo();

            // We're duplicating the E&O entity here, because any changes to the
            // branch's E&O shouldn't be reflected on the company's E&O and vice versa.
            $this->transfer($company->getEo(), $eo, [
                'id',
                'document'
            ]);

            $branch->setEo($eo);

            if ($document = $company->getEo()->getDocument()) {
                $eo->setDocument($document);
            }

            $this->entityManager->persist($eo);
        }

        $this->entityManager->persist($branch);
        $this->entityManager->flush();

        return $branch;
    }

    /**
     * @param int $companyId
     * @param BranchPersistable $persistable
     * @return Branch
     */
    public function create($companyId, BranchPersistable $persistable)
    {
        /**
         * @var Company $company
         */
        $company = $this->entityManager->getReference(Company::class, $companyId);

        (new BranchValidator($this->container))
            ->setCurrentCompany($company)
            ->validate($persistable);

        $branch = new Branch();
        $branch->setCompany($company);

        $this->save($persistable, $branch);

        return $branch;
    }

    /**
     * @param int $branchId
     * @return Branch
     */
    public function get($branchId)
    {
        return $this->entityManager->find(Branch::class, $branchId);
    }

    /**
     * @param int $branchId
     * @param BranchPersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($branchId, BranchPersistable $persistable, UpdateOptions $options = null)
    {
        if ($options === null) {
            $options = new UpdateOptions();
        }

        /**
         * @var Branch $branch
         */
        $branch = $this->entityManager->getReference(Branch::class, $branchId);

        (new BranchValidator($this->container))
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->setCurrentCompany($branch->getCompany())
            ->setCurrentBranch($branch)
            ->validateWithBranch($persistable, $branch);

        $this->save($persistable, $branch);
    }

    /**
     * @param BranchPersistable $persistable
     * @param Branch $branch
     */
    private function save(BranchPersistable $persistable, Branch $branch)
    {
        $this->transfer($persistable, $branch, [
            'ignore' => [
                'state',
                'eo'
            ]
        ]);

        if ($state = $persistable->getState()) {
            /**
             * @var State
             */
            $state = $this->entityManager->getReference(State::class, $state);
            $branch->setState($state);
        }

        if ($eoPersistable = $persistable->getEo()) {
            $eo = $branch->getEo() ?? new Eo();

            $this->transfer($eoPersistable, $eo, [
                'ignore' => [
                    'document'
                ]
            ]);

            if ($eoPersistable->getDocument()) {
                /**
                 * @var Document $document
                 */
                $document = $this->entityManager->getReference(
                    Document::class, $eoPersistable->getDocument()->getId()
                );

                $eo->setDocument($document);
            }

            if ($eo->getId() === null) {
                $this->entityManager->persist($eo);
            }

            $branch->setEo($eo);
        }

        if ($branch->getId() === null) {
            $this->entityManager->persist($branch);
        }

        $this->entityManager->flush();
    }

    /**
     * @param int $companyId
     * @return Branch[]
     */
    public function getAll($companyId)
    {
        return $this->entityManager->getRepository(Branch::class)->retrieveAll([
            'company' => $companyId
        ]);
    }

    /**
     * @param int $branchId
     */
    public function delete($branchId)
    {
        /**
         * @var Branch
         */
        $branch = $this->entityManager->find(Branch::class, $branchId);

        if ($branch->isDefault()) {
            throw new PresentableException('Default branch can not be deleted.');
        }

        if (! $branch->getStaff()->isEmpty()) {
            throw new PresentableException('All employees must be removed before branch can be deleted.');
        }

        $this->entityManager->remove($branch);
        $this->entityManager->flush();
    }

    /**
     * @param string $taxId
     * @param int $excludeCompanyId
     * @return bool
     */
    public function existsWithTaxId($taxId, $excludeCompanyId = null)
    {
        $criteria = ['taxId' => $taxId];

        if ($excludeCompanyId) {
            $criteria['company'] = ['!=', $excludeCompanyId];
        }

        return $this->entityManager->getRepository(Branch::class)->exists($criteria);
    }

    /**
     * @param int $branchId
     * @return bool
     */
    public function exists($branchId)
    {
        return $this->entityManager->getRepository(Branch::class)->exists(['id' => $branchId]);
    }
}
