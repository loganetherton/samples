<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraisal\Notifications\UpdateProcessStatusNotification;
use DateTime;

class UpdateProcessStatusHandler extends AbstractOrderHandler
{
	/**
	 * @param UpdateProcessStatusNotification|AbstractNotification $notification
	 * @return string
	 */
	protected function getSubject(AbstractNotification $notification)
	{
		return $this->prepareProcessStatus($notification->getNewProcessStatus())
			.' - Order on '.$notification->getOrder()->getProperty()->getDisplayAddress();
	}

	/**
	 * @param UpdateProcessStatusNotification|AbstractNotification $notification
	 * @return array
	 */
	protected function getData(AbstractNotification $notification)
	{
		$data = parent::getData($notification);

		$data['newProcessStatus'] = $this->prepareProcessStatus($notification->getNewProcessStatus());
		$data['oldProcessStatus'] = $this->prepareProcessStatus($notification->getOldProcessStatus());

		$processStatus = $notification->getNewProcessStatus();

		if ($processStatus->is(ProcessStatus::INSPECTION_SCHEDULED)){
			$data['scheduledAt'] = $this->prepareDateTime(
				$notification->getExtra()[UpdateProcessStatusNotification::EXTRA_SCHEDULED_AT]);

			$data['estimatedCompletionDate'] = $this->prepareDateTime(
				$notification->getExtra()[UpdateProcessStatusNotification::EXTRA_ESTIMATED_COMPLETION_DATE]);
		}

		if ($processStatus->is(ProcessStatus::INSPECTION_COMPLETED)){
			$data['completedAt'] = $this->prepareDateTime(
				$notification->getExtra()[UpdateProcessStatusNotification::EXTRA_COMPLETED_AT]);

			$data['estimatedCompletionDate'] = $this->prepareDateTime(
				$notification->getExtra()[UpdateProcessStatusNotification::EXTRA_ESTIMATED_COMPLETION_DATE]);
		}

		if ($processStatus->is(ProcessStatus::ON_HOLD)){
			$data['explanation'] = $notification->getOrder()->getComment();
		}

		return $data;
	}

	/**
	 * @param DateTime $datetime
	 * @return string
	 */
	private function prepareDateTime(DateTime $datetime  = null){
		if ($datetime === null){
			return '';
		}

		return $datetime->format('m/d/Y');
	}

	/**
	 * @return string
	 */
	protected function getTemplate()
	{
		return 'emails.appraisal.update_process_status';
	}

	/**
	 * @param ProcessStatus $processStatus
	 * @return string
	 */
	private function prepareProcessStatus(ProcessStatus $processStatus)
	{
		return ucwords(str_replace('-', ' ', (string)$processStatus));
	}
}
