<?php
namespace ValuePad\Core\Appraisal\Services;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Entities\Revision;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Notifications\RevisionRequestNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification;
use ValuePad\Core\Appraisal\Persistables\RevisionPersistable;
use ValuePad\Core\Appraisal\Validation\RevisionValidator;
use DateTime;
use ValuePad\Core\Support\Service\AbstractService;

class RevisionService extends AbstractService
{
	use CommonsTrait;

    /**
     * @param int $id
     * @return Revision
     */
    public function get($id)
    {
        return $this->entityManager->find(Revision::class, $id);
    }

	/**
	 * @param int $orderId
	 * @param RevisionPersistable $persistable
	 * @return Revision
	 */
	public function create($orderId, RevisionPersistable $persistable)
	{
		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $orderId);

		(new RevisionValidator())->validate($persistable);

		$revision = new Revision();

		$revision->setOrder($order);

		if ($createdAt = $this->environment->getLogCreatedAt()){
			$revision->setCreatedAt($createdAt);
		} else {
			$revision->setCreatedAt(new DateTime());
		}

		if ($persistable->getChecklist()){
			$revision->setChecklist($persistable->getChecklist());
		}

		$revision->setMessage($persistable->getMessage());
		$this->entityManager->persist($revision);

		list($oldProcessStatus, $newProcessStatus) = $this->handleProcessStatusTransitionInMemory(
			$order, new ProcessStatus(ProcessStatus::REVISION_PENDING), $this->container);

		$this->entityManager->flush();

		$notification = new RevisionRequestNotification($order, $revision);
		$notification->setUpdateProcessStatusNotification(
			new UpdateProcessStatusNotification($order, $oldProcessStatus, $newProcessStatus));

		$this->notify($notification);

		return $revision;
	}

	/**
	 * @param int $orderId
	 * @return Revision[]
	 */
	public function getAll($orderId)
	{
		return $this->entityManager->getRepository(Revision::class)->findBy(['order' => $orderId]);
	}

	/**
	 * @param int $orderId
	 */
	public function deleteAll($orderId)
	{
		$revisions = $this->entityManager->getRepository(Revision::class)->findBy(['order' => $orderId]);

		foreach ($revisions as $revision){
			$this->entityManager->remove($revision);
		}

		$this->entityManager->flush();
	}
}
