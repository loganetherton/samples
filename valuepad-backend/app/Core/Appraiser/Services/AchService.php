<?php
namespace ValuePad\Core\Appraiser\Services;

use ValuePad\Core\Appraiser\Notifications\UpdateAchNotification;
use ValuePad\Core\Appraiser\Entities\Ach;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Persistables\AchPersistable;
use ValuePad\Core\Appraiser\Validation\AchValidator;
use ValuePad\Core\Support\Service\AbstractService;

class AchService extends AbstractService
{
	/**
	 * @param int $appraiserId
	 * @param AchPersistable $persistable
	 * @return Ach
	 */
	public function replace($appraiserId, AchPersistable $persistable)
	{
		(new AchValidator())->validate($persistable);

        /**
         * @var Appraiser $appraiser
         */
        $appraiser = $this->entityManager->find(Appraiser::class, $appraiserId);

		/**
		 * @var Ach $ach
		 */
		$ach = $appraiser->getAch();

		if ($ach === null){
			$ach = new Ach();
		}


		$this->transfer($persistable, $ach);

		if ($ach->getId() === null){
			$this->entityManager->persist($ach);
		}

		$appraiser->setAch($ach);

		$this->entityManager->flush();

        $this->notify(new UpdateAchNotification($ach, $appraiser));

		return $ach;
	}

	/**
	 * @param int $appraiserId
	 * @return Ach
	 */
	public function getExistingOrEmpty($appraiserId)
	{
        /**
         * @var Appraiser $appraiser
         */
        $appraiser = $this->entityManager->find(Appraiser::class, $appraiserId);

        return $appraiser->getAch() ?? new Ach();
	}
}
