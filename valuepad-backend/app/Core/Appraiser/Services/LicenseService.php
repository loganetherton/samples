<?php
namespace ValuePad\Core\Appraiser\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Entities\License;
use ValuePad\Core\Appraiser\Notifications\CreateLicenseNotification;
use ValuePad\Core\Appraiser\Notifications\DeleteLicenseNotification;
use ValuePad\Core\Appraiser\Notifications\UpdateLicenseNotification;
use ValuePad\Core\Appraiser\Options\CreateLicenseOptions;
use ValuePad\Core\Appraiser\Options\UpdateLicenseOptions;
use ValuePad\Core\Appraiser\Persistables\LicensePersistable;
use ValuePad\Core\Appraiser\Validation\LicenseValidator;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Assignee\Support\CoverageManagement;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\Appraiser\Entities\Coverage;

class LicenseService extends AbstractService
{
    /**
     * @var AscService
     */
    private $ascService;

    /**
     * @var StateService
     */
    private $stateService;

	/**
	 * @var CoverageManagement
	 */
	private $coverageManagement;

    /**
     * @param AscService $ascService
     * @param StateService $stateService
     */
    public function initialize(AscService $ascService, StateService $stateService)
    {
        $this->ascService = $ascService;
        $this->stateService = $stateService;
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
     * @param int $appraiserId
     * @return License[]
     */
    public function getAll($appraiserId)
    {
        return $this->entityManager->getRepository(License::class)->findBy([
            'appraiser' => $appraiserId
        ]);
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

        if ($license->isPrimary()) {
            throw new PresentableException('The primary license cannot be deleted.');
        }

		$this->coverageManagement->clearCoverages($license);

		$license->detachDocument();

		$ascAppraiser = $this->ascService->getByLicenseNumber($license->getNumber());

		$appraiser = $license->getAppraiser();

		$appraiser->removeRelationWithAscAppraiser($ascAppraiser);
		$appraiser->removeLicense($license);

        $this->notify(new DeleteLicenseNotification($license));

		$this->entityManager->remove($license);

        $this->entityManager->flush();
    }

    /**
     * @param int $appraiserId
     * @param LicensePersistable $persistable
	 * @param CreateLicenseOptions $options
     * @return License
     */
    public function create($appraiserId, LicensePersistable $persistable, CreateLicenseOptions $options = null)
    {
		if ($options === null){
			$options = new CreateLicenseOptions();
		}

      	/**
		 * @var Appraiser $appraiser
		 */
		$appraiser = $this->entityManager->find(Appraiser::class, $appraiserId);


		if (!$options->isTrusted()){
			(new LicenseValidator($this->container))
				->setCurrentAppraiser($appraiser)
				->validate($persistable);
		}

        $license = new License();

        $this->exchange($persistable, $license);

        $license->setAppraiser($appraiser);

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

		$ascAppraiser = $this->ascService->getByLicenseNumber($license->getNumber());

		$appraiser->addRelationWithAscAppraiser($ascAppraiser);

		$this->entityManager->flush();

        $this->notify(new CreateLicenseNotification($license));

        return $license;
    }

    /**
     * @param int $id
     * @param LicensePersistable $persistable
     * @param UpdateLicenseOptions $options
     */
    public function update($id, LicensePersistable $persistable, UpdateLicenseOptions $options = null)
    {
        if ($options === null) {
            $options = new UpdateLicenseOptions();
        }

		/**
		 * @var License $license
		 */
		$license = $this->entityManager->find(License::class, $id);

		if (!$options->isTrusted()){
			(new LicenseValidator($this->container))
				->setForcedProperties($options->getPropertiesScheduledToClear())
				->validateWithLicense($persistable, $license);
		}

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

        $this->entityManager->flush();

        $this->notify(new UpdateLicenseNotification($license));
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
                'coverages'
            ],
            'nullable' => $nullable
        ]);
    }
}
