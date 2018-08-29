<?php
namespace ValuePad\Core\Appraisal\Services;

use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Entities\AdditionalDocument;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Persistables\AdditionalDocumentPersistable;
use ValuePad\Core\Customer\Entities\AdditionalDocumentType;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Invitation\Enums\Status;
use ValuePad\Core\Invitation\Services\InvitationService;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Support\Service\ContainerInterface;
use DateTime;

trait CommonsTrait
{
	/**
	 * @param Order $order
	 * @param ContainerInterface $container
	 */
	protected function handleInvitationInOrder(Order $order, ContainerInterface $container)
	{
		/**
		 * @var InvitationService $invitationService
		 */
		$invitationService = $container->get(InvitationService::class);

		/**
		 * @var EntityManagerInterface $entityManager
		 */
		$entityManager = $container->get(EntityManagerInterface::class);

		if (($invitation = $order->getInvitation()) && $invitation->getStatus()->is(Status::PENDING)){
			$invitationService->accept($invitation->getId(), $order->getAssignee()->getId());
			$order->setInvitation(null);
			$entityManager->flush();
		}
	}

	/**
	 * @param Order $order
	 * @param AdditionalDocumentPersistable $persistable
	 * @param ContainerInterface $container
	 * @return AdditionalDocument
	 */
	protected function createAdditionalDocumentInMemory(
		Order $order,
		AdditionalDocumentPersistable $persistable,
		ContainerInterface $container
	)
	{
		/**
		 * @var EnvironmentInterface $environment
		 */
		$environment = $container->get(EnvironmentInterface::class);

		/**
		 * @var EntityManagerInterface $entityManager
		 */
		$entityManager = $container->get(EntityManagerInterface::class);

		$additionalDocument = new AdditionalDocument();
		$additionalDocument->setOrder($order);

		if ($createdAt = $environment->getLogCreatedAt()){
			$additionalDocument->setCreatedAt($createdAt);
		} else {
			$additionalDocument->setCreatedAt(new DateTime());
		}

		$additionalDocument->setLabel($persistable->getLabel());

		if ($persistable->getType()){

			/**
			 * @var AdditionalDocumentType $type
			 */
			$type = $entityManager->getReference(AdditionalDocumentType::class, $persistable->getType());

			$additionalDocument->setType($type);
		}

		/**
		 * @var Document $document
		 */
		$document = $entityManager->getReference(Document::class, $persistable->getDocument()->getId());

		$additionalDocument->setDocument($document);

		return $additionalDocument;
	}

	/**
	 * @param Order $order
	 * @param ProcessStatus $newProcessStatus
	 * @param ContainerInterface $container
	 * @return ProcessStatus[]
	 */
	protected function handleProcessStatusTransitionInMemory(Order $order, ProcessStatus $newProcessStatus, ContainerInterface $container)
	{
		$oldProcessStatus = $order->getProcessStatus();

		/**
		 * @var EnvironmentInterface $environment
		 */
		$environment = $container->get(EnvironmentInterface::class);

		$occurredAt = $environment->getLogCreatedAt() ?? new DateTime();

		if ($newProcessStatus->is(ProcessStatus::ACCEPTED)){
			$order->setAcceptedAt($occurredAt);
		}

		if ($newProcessStatus->is(ProcessStatus::REVISION_PENDING)){
			$order->setRevisionReceivedAt($occurredAt);
		}

		if ($newProcessStatus->is(ProcessStatus::COMPLETED)){
			$order->setCompletedAt($occurredAt);
		}

		// Set tax ID at completion
        if ($newProcessStatus->is(ProcessStatus::COMPLETED)) {
            $order->setTinAtCompletion($order->getAssigneeTaxId());
        }

		$order->setProcessStatus($newProcessStatus);

		return [$oldProcessStatus, $newProcessStatus];
	}
}
