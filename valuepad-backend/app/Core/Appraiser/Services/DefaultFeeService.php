<?php
namespace ValuePad\Core\Appraiser\Services;

use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Entities\DefaultFee;
use ValuePad\Core\Appraiser\Validation\CreateDefaultFeeValidator;
use ValuePad\Core\Assignee\Persistables\FeePersistable;
use ValuePad\Core\Assignee\Services\FeeCommonsTrait;
use ValuePad\Core\Assignee\Validation\UpdateFeeValidator;
use ValuePad\Core\JobType\Entities\JobType;
use ValuePad\Core\JobType\Services\JobTypeService;
use ValuePad\Core\Shared\Options\UpdateOptions;
use ValuePad\Core\Support\Service\AbstractService;

class DefaultFeeService extends AbstractService
{
	use FeeCommonsTrait;

	/**
	 * @param int $appraiserId
	 * @return DefaultFee[]
	 */
	public function getAll($appraiserId)
	{
		return $this->entityManager->getRepository(DefaultFee::class)->findBy([
			'appraiser' => $appraiserId
		]);
	}

	/**
	 * @param int $appraiserId
	 * @param FeePersistable $persistable
	 * @return DefaultFee
	 */
	public function create($appraiserId, FeePersistable $persistable)
	{
		/**
		 * @var Appraiser $appraiser
		 */
		$appraiser = $this->entityManager->getReference(Appraiser::class, $appraiserId);

		(new CreateDefaultFeeValidator($this->container, $appraiser))->validate($persistable);

		$fee = $this->createInMemory($appraiser, $persistable);

		$this->entityManager->persist($fee);

		$this->entityManager->flush();

		return $fee;
	}

	/**
	 * @param $appraiserId
	 * @param FeePersistable[] $persistables
	 * @return DefaultFee[]
	 */
	public function createBulk($appraiserId, array $persistables)
	{
		$hash = $this->prepareJobTypeAmountHash($persistables);

		$this->verifyJobTypesUniqueness($hash, $persistables);
		$this->verifyFeeAmounts($hash);

		$jobTypesIds = array_keys($hash);

		/**
		 * @var JobTypeService $jobTypeService
		 */
		$jobTypeService = $this->container->get(JobTypeService::class);

		if (!$jobTypeService->existSelected($jobTypesIds)){
			throw new PresentableException('Unable to find some of the provided job types.');
		}

		/**
		 * @var AppraiserService $appraiserService
		 */
		$appraiserService = $this->container->get(AppraiserService::class);

		if ($appraiserService->hasDefaultFeesWithJobTypes($appraiserId, $jobTypesIds)){
			throw new PresentableException('Default fees have been already set for some of the provided job types.');
		}

		$fees = [];

		/**
		 * @var Appraiser $appraiser
		 */
		$appraiser = $this->entityManager->getReference(Appraiser::class, $appraiserId);

		foreach ($persistables as $persistable){
			$fee = $this->createInMemory($appraiser, $persistable);

			$this->entityManager->persist($fee);

			$fees[] = $fee;
		}

		$this->entityManager->flush();

		return $fees;
	}

	/**
	 * @param Appraiser $appraiser
	 * @param FeePersistable $persistable
	 * @return DefaultFee
	 */
	private function createInMemory(Appraiser $appraiser, FeePersistable $persistable)
	{
		$fee = new DefaultFee();

		/**
		 * @var JobType $jobType
		 */
		$jobType = $this->entityManager->getReference(JobType::class, $persistable->getJobType());

		$fee->setAppraiser($appraiser);
		$fee->setJobType($jobType);

		$fee->setAmount($persistable->getAmount());

		return $fee;
	}

	/**
	 * @param int $id
	 * @param FeePersistable $persistable
	 * @param UpdateOptions $options
	 */
	public function update($id, FeePersistable $persistable, UpdateOptions  $options = null)
	{
		if ($options === null){
			$options = new UpdateOptions();
		}

		(new UpdateFeeValidator())
			->setForcedProperties($options->getPropertiesScheduledToClear())
			->validate($persistable);

		/**
		 * @var DefaultFee $fee
		 */
		$fee = $this->entityManager->find(DefaultFee::class, $id);

		$fee->setAmount($persistable->getAmount());

		$this->entityManager->flush();
	}

	/**
	 * @param int $appraiserId
	 * @param array $amounts - id => amount
	 */
	public function updateBulkOwningByAppraiser($appraiserId, array $amounts)
	{
		$this->verifyFeeAmounts($amounts);

		/**
		 * @var DefaultFee[] $fees
		 */
		$fees = $this->entityManager->getRepository(DefaultFee::class)
			->retrieveAll(['appraiser' => $appraiserId, 'id' => ['in', array_keys($amounts)]]);

		foreach ($fees as $fee){
			$fee->setAmount($amounts[$fee->getId()]);
		}

		$this->entityManager->flush();
	}

	/**
	 * @param $appraiserId
	 * @param array $ids
	 */
	public function deleteBulkOwningByAppraiser($appraiserId, array $ids)
	{
		/**
		 * @var Appraiser $appraiser
		 */
		$appraiser = $this->entityManager->getReference(Appraiser::class, $appraiserId);

		foreach ($ids as $id){
			/**
			 * @var DefaultFee $fee
			 */
			$fee = $this->entityManager->getReference(DefaultFee::class, $id);
			$appraiser->removeDefaultFee($fee);
		}

		$this->entityManager->getRepository(DefaultFee::class)->delete([
			'appraiser' => $appraiserId, 'id' => ['in', $ids]
		]);
	}

	/**
	 * @param int $id
	 */
	public function delete($id)
	{
		/**
		 * @var DefaultFee $fee
		 */
		$fee = $this->entityManager->getReference(DefaultFee::class, $id);

		$appraiser = $fee->getAppraiser();

		$appraiser->removeDefaultFee($fee);

		$this->entityManager->remove($fee);

		$this->entityManager->flush();
	}
}
