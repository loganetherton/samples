<?php
namespace ValuePad\Core\Amc\Services;
use ValuePad\Core\Amc\Entities\Alias;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\Coverage;
use ValuePad\Core\Amc\Entities\License;
use ValuePad\Core\Amc\Persistables\LicensePersistable;
use ValuePad\Core\Amc\Validation\LicenseValidator;
use ValuePad\Core\Assignee\Support\CoverageManagement;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

class LicenseService extends AbstractService
{
    /**
     * @var CoverageManagement
     */
    private $coverageManagement;

    public function initialize()
    {
        $this->coverageManagement = new CoverageManagement($this->entityManager, Coverage::class);
    }

    /**
     * @param int $id
     * @return License
     */
    public function get($id)
    {
        return $this->entityManager->find(License::class, $id);
    }

    /**
     * @param int $amcId
     * @return License[]
     */
    public function getAll($amcId)
    {
        return $this->entityManager->getRepository(License::class)->findBy([
            'amc' => $amcId
        ]);
    }

    /**
     * @param int $amcId
     * @param LicensePersistable $persistable
     * @return License
     */
    public function create($amcId, LicensePersistable $persistable)
    {
        /**
         * @var Amc $amc
         */
        $amc = $this->entityManager->find(Amc::class, $amcId);

        (new LicenseValidator($this->container))
            ->setCurrentAmc($amc)
            ->validate($persistable);

        $license = new License();

        $this->exchange($persistable, $license);

        $license->setAmc($amc);

        /**
         * @var State $state
         */
        $state = $this->entityManager->getReference(State::class, $persistable->getState());

        $license->setState($state);

        if ($persistable->getDocument()){

            /**
             * @var Document $document
             */
            $document = $this->entityManager->getReference(Document::class, $persistable->getDocument()->getId());

            $license->setDocument($document);
        }

        $this->entityManager->persist($license);

        $this->entityManager->flush();

        if ($persistable->getCoverages()){
            $this->coverageManagement->addCoverages($license, $persistable->getCoverages());
        }

        $this->saveAlias($license, $persistable);

        $this->entityManager->flush();

        return $license;
    }

    /**
     * @param int $id
     * @param LicensePersistable $persistable
     * @param UpdateOptions $options
     */
    public function update($id, LicensePersistable $persistable, UpdateOptions $options = null)
    {
        if ($options === null) {
            $options = new UpdateOptions();
        }

        /**
         * @var License $license
         */
        $license = $this->entityManager->find(License::class, $id);

        (new LicenseValidator($this->container))
            ->setForcedProperties($options->getPropertiesScheduledToClear())
            ->validateWithLicense($persistable, $license);

        $this->exchange($persistable, $license, $options->getPropertiesScheduledToClear());

        if ($persistable->getDocument()){

            /**
             * @var Document $document
             */
            $document = $this->entityManager->getReference(Document::class, $persistable->getDocument()->getId());
            $license->setDocument($document);
        } elseif ($options->isPropertyScheduledToClear('document')){
            $license->detachDocument();
        }

        if ($persistable->getCoverages() !== null){
            $this->coverageManagement->clearCoverages($license);
            $this->coverageManagement->addCoverages($license, $persistable->getCoverages());
        }

        $this->saveAlias($license, $persistable);

        if ($options->isPropertyScheduledToClear('alias')) {
            /**
             * @var Alias $alias
             */
            $alias = $license->getAlias();

            $license->removeAlias();

            if ($alias) {
                $this->entityManager->remove($alias);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        /**
         * @var License $license
         */
        $license = $this->entityManager->find(License::class, $id);

        $this->coverageManagement->clearCoverages($license);

        $license->detachDocument();

        $this->entityManager->remove($license);

        $this->entityManager->flush();
    }

    /**
     * @param LicensePersistable $persistable
     * @param License $license
     * @param array $nullable
     */
    private function exchange(LicensePersistable $persistable, License $license, array $nullable = [])
    {
        $this->transfer($persistable, $license, [
            'ignore' => [
                'state',
                'document',
                'coverages',
                'alias'
            ],
            'nullable' => $nullable
        ]);
    }

    /**
     * @param string $number
     * @param string $state
     */
    public function existsWithNumberInState($number, $state)
    {
        return $this->entityManager->getRepository(License::class)
            ->exists(['number' => $number, 'state' => $state]);
    }

    /**
     * Save alias attached to the license
     *
     * @param License $license
     * @param LicensePersistable $persistable
     */
    private function saveAlias(License $license, LicensePersistable $persistable)
    {
        if ($persistable->getAlias()) {
            $alias = $license->getAlias();

            if (!$alias) {
                $alias = new Alias();
                $this->entityManager->persist($alias);
            }

            $this->transfer($persistable->getAlias(), $alias, ['ignore' => ['state']]);

            if ($persistable->getAlias()->getState()) {
                /**
                 * @var State $state
                 */
                $state = $this->entityManager->getReference(State::class, $persistable->getAlias()->getState());

                $alias->setState($state);
            }

            $license->setAlias($alias);
        }
    }
}
