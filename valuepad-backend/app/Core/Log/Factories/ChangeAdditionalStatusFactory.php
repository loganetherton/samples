<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Core\Log\Extras\AdditionalStatusExtra;
use ValuePad\Core\Log\Extras\Extra;

class ChangeAdditionalStatusFactory extends AbstractFactory
{
	/**
	 * @param ChangeAdditionalStatusNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = parent::create($notification);

		$log->setAction(new Action(Action::CHANGE_ADDITIONAL_STATUS));

		$extra = $log->getExtra();

		if ($oldAdditionalStatus = $notification->getOldAdditionalStatus()){
			$oldAdditionalStatus = AdditionalStatusExtra::fromAdditionalStatus($oldAdditionalStatus);
		}

		$extra[Extra::OLD_ADDITIONAL_STATUS] = $oldAdditionalStatus;
		$extra[Extra::OLD_ADDITIONAL_STATUS_COMMENT] = $notification->getOldAdditionalStatusComment();

		if ($newAdditionalStatus = $notification->getNewAdditionalStatus()){
			$newAdditionalStatus = AdditionalStatusExtra::fromAdditionalStatus($newAdditionalStatus);
		}

		$extra[Extra::NEW_ADDITIONAL_STATUS] = $newAdditionalStatus;
		$extra[Extra::NEW_ADDITIONAL_STATUS_COMMENT] = $notification->getNewAdditionalStatusComment();

		return $log;
	}
}
