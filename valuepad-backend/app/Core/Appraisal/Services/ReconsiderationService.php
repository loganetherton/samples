<?php
namespace ValuePad\Core\Appraisal\Services;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Entities\Reconsideration;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Notifications\ReconsiderationRequestNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification;
use ValuePad\Core\Appraisal\Persistables\ReconsiderationPersistable;
use ValuePad\Core\Appraisal\Validation\ReconsiderationValidator;
use DateTime;
use ValuePad\Core\Support\Service\AbstractService;

class ReconsiderationService extends AbstractService
{
	use CommonsTrait;

    /**
     * @param int $id
     * @return Reconsideration
     */
    public function get($id)
    {
        return $this->entityManager->find(Reconsideration::class, $id);
    }

	/**
	 * @param $orderId
	 * @param ReconsiderationPersistable $persistable
	 * @return Reconsideration
	 */
	public function create($orderId, ReconsiderationPersistable $persistable)
	{
        /**
         * @var Order $order
         */
        $order = $this->entityManager->find(Order::class, $orderId);

		(new ReconsiderationValidator($this->container, $order->getCustomer()))->validate($persistable);


		$reconsideration = new Reconsideration();

		$reconsideration->setComment($persistable->getComment());

        if ($document = $persistable->getDocument()){
            $document = $this->createAdditionalDocumentInMemory($order, $document, $this->container);
            $this->entityManager->persist($document);
            $this->entityManager->flush();
            $reconsideration->setDocument($document);
        }

        if ($documents = $persistable->getDocuments()) {
            $persistedDocuments = [];
            foreach ($documents as $document) {
                $document = $this->createAdditionalDocumentInMemory($order, $document, $this->container);
                $this->entityManager->persist($document);
                $persistedDocuments[] = $document;
            }

            $this->entityManager->flush();
            $reconsideration->setDocuments($persistedDocuments);
        }

		if ($createdAt = $this->environment->getLogCreatedAt()){
			$reconsideration->setCreatedAt($createdAt);
		} else {
			$reconsideration->setCreatedAt(new DateTime());
		}

		$comparables = $persistable->getComparables();

        if ($comparables !== null){
            $reconsideration->setComparables($comparables);
        }

        $reconsideration->setOrder($order);

		$this->entityManager->persist($reconsideration);

		list($oldProcessStatus, $newProcessStatus) =  $this->handleProcessStatusTransitionInMemory(
			$order, new ProcessStatus(ProcessStatus::REVISION_PENDING), $this->container);

		$this->entityManager->flush();

		$notification = new ReconsiderationRequestNotification($order, $reconsideration);

		$notification->setUpdateProcessStatusNotification(
			new UpdateProcessStatusNotification($order,  $oldProcessStatus, $newProcessStatus));

		$this->notify($notification);

		return $reconsideration;
	}

	/**
	 * @param int $orderId
	 * @return Reconsideration[]
	 */
	public function getAll($orderId)
	{
		return $this->entityManager
			->getRepository(Reconsideration::class)
			->findBy(['order' => $orderId]);
	}

	/**
	 * @param int $orderId
	 */
	public function deleteAll($orderId)
	{
		$this->entityManager->getRepository(Reconsideration::class)->delete(['order' => $orderId]);
	}
}
