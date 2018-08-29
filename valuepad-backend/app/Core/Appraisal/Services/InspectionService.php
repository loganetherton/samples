<?php
namespace ValuePad\Core\Appraisal\Services;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification;
use ValuePad\Core\Appraisal\Options\InspectionOptions;
use ValuePad\Core\Appraisal\Validation\InspectionValidator;
use ValuePad\Core\Support\Service\AbstractService;
use DateTime;

class InspectionService extends AbstractService
{
	/**
	 * @param int $orderId
	 * @param DateTime $scheduledAt
	 * @param DateTime $estimatedCompletionDate
     * @param InspectionOptions $options
	 */
	public function schedule(
		$orderId,
		DateTime $scheduledAt,
		DateTime $estimatedCompletionDate,
        InspectionOptions $options = null
	)
	{
        if ($options === null){
            $options = new InspectionOptions();
        }

		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $orderId);

		$oldProcessStatus = $order->getProcessStatus();
		$newProcessStatus = new ProcessStatus(ProcessStatus::INSPECTION_SCHEDULED);

		(new InspectionValidator(
			$order->getDueDate(),
			$order->getCustomer()->getSettings(),
			false,
			$this->environment
		))
            ->setBypassDatesValidation($options->getBypassDatesValidation())
            ->validate([
                'scheduledAt' => $scheduledAt,
                'estimatedCompletionDate' => $estimatedCompletionDate
            ]);

		$order->setProcessStatus($newProcessStatus);
		$order->setInspectionScheduledAt($scheduledAt);
		$order->setEstimatedCompletionDate($estimatedCompletionDate);

		$this->entityManager->flush();

		$this->notify(new UpdateProcessStatusNotification(
			$order,
			$oldProcessStatus,
			$newProcessStatus,
			[
				UpdateProcessStatusNotification::EXTRA_SCHEDULED_AT => $scheduledAt,
				UpdateProcessStatusNotification::EXTRA_ESTIMATED_COMPLETION_DATE => $estimatedCompletionDate
			]
		));
	}

	/**
	 * @param int $orderId
	 * @param DateTime $completedAt
	 * @param DateTime $estimatedCompletionDate
     * @param InspectionOptions $options
	 */
	public function complete(
		$orderId,
		DateTime $completedAt,
		DateTime $estimatedCompletionDate,
        InspectionOptions $options = null
	)
	{
        if ($options === null){
            $options = new InspectionOptions();
        }

		/**
		 * @var Order $order
		 */
		$order = $this->entityManager->find(Order::class, $orderId);

		$oldProcessStatus = $order->getProcessStatus();
		$newProcessStatus = new ProcessStatus(ProcessStatus::INSPECTION_COMPLETED);


		(new InspectionValidator(
			$order->getDueDate(),
			$order->getCustomer()->getSettings(),
			true,
			$this->environment
		))
            ->setBypassDatesValidation($options->getBypassDatesValidation())
            ->validate([
                'completedAt' => $completedAt,
                'estimatedCompletionDate' => $estimatedCompletionDate
            ]);

		$order->setProcessStatus($newProcessStatus);
		$order->setInspectionCompletedAt($completedAt);
		$order->setEstimatedCompletionDate($estimatedCompletionDate);

		$this->entityManager->flush();

		$this->notify(new UpdateProcessStatusNotification(
			$order,
			$oldProcessStatus,
			$newProcessStatus,
			[
				UpdateProcessStatusNotification::EXTRA_COMPLETED_AT => $completedAt,
				UpdateProcessStatusNotification::EXTRA_ESTIMATED_COMPLETION_DATE => $estimatedCompletionDate
			]
		));
	}
}
