<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;

class UpdateProcessStatusNotification extends AbstractNotification
{
	const EXTRA_COMPLETED_AT = 'completedAt';
	const EXTRA_SCHEDULED_AT = 'scheduledAt';
	const EXTRA_ESTIMATED_COMPLETION_DATE = 'estimatedCompletionDate';

	/**
	 * @var ProcessStatus
	 */
	private $oldProcessStatus;

	/**
	 * @var ProcessStatus
	 */
	private $newProcessStatus;

	/**
	 * @var array
	 */
	private $extra = [];

	/**
	 * @param Order $order
	 * @param ProcessStatus $old
	 * @param ProcessStatus $new
	 * @param array $extra
	 */
	public function __construct(Order $order, ProcessStatus $old, ProcessStatus $new, array $extra = [])
	{
		parent::__construct($order);

		$this->oldProcessStatus = $old;
		$this->newProcessStatus = $new;
		$this->extra = $extra;
	}

	/**
	 * @return ProcessStatus
	 */
	public function getOldProcessStatus()
	{
		return $this->oldProcessStatus;
	}

	/**
	 * @return ProcessStatus
	 */
	public function getNewProcessStatus()
	{
		return $this->newProcessStatus;
	}

	/**
	 * @return array
	 */
	public function getExtra()
	{
		return $this->extra;
	}
}
