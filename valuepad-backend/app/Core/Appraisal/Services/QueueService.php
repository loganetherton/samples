<?php
namespace ValuePad\Core\Appraisal\Services;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Enums\Queue;
use ValuePad\Core\Appraisal\Objects\Counter;
use ValuePad\Core\Appraisal\Options\FetchOrdersOptions;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Criteria;
use ValuePad\Core\Support\Service\AbstractService;

class QueueService extends AbstractService
{
	/**
	 * @var OrderService
	 */
	private $orderService;

	/**
	 * @param OrderService $orderService
	 */
	public function initialize(OrderService $orderService)
	{
		$this->orderService = $orderService;
	}

	/**
	 * @param int $assigneeId
	 * @param Queue $queue
	 * @param FetchOrdersOptions $options
	 * @return Order[]
	 */
	public function getAllByAssigneeId($assigneeId, Queue $queue, FetchOrdersOptions $options = null)
	{
		$options->setCriteria($this->modifyCriteriaByQueue($options->getCriteria(), $queue));

		return $this->orderService->getAllByAssigneeId($assigneeId, $options);
	}

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @param Queue $queue
     * @param FetchOrdersOptions $options
     * @return Order[]
     */
	public function getAllByCustomerAndAssigneeIds($customerId, $assigneeId, Queue $queue, FetchOrdersOptions $options = null)
    {
        $options->setCriteria($this->modifyCriteriaByQueue($options->getCriteria(), $queue));

        return $this->orderService->getAllByCustomerAndAssigneeIds($customerId, $assigneeId, $options);
    }

	/**
	 * @param int $assigneeId
	 * @param Queue $queue
	 * @param Criteria[] $criteria
	 * @return int
	 */
	public function getTotalByAssigneeId($assigneeId, Queue $queue, array $criteria = [])
	{
		$criteria = $this->modifyCriteriaByQueue($criteria, $queue);

		return $this->orderService->getTotalByAssigneeId($assigneeId, $criteria);
	}

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @param Queue $queue
     * @param Criteria[] $criteria
     * @return int
     */
	public function getTotalByCustomerAndAssigneeIds($customerId, $assigneeId, Queue $queue, array $criteria = [])
    {
        $criteria = $this->modifyCriteriaByQueue($criteria, $queue);

        return $this->orderService->getTotalByCustomerAndAssigneeIds($customerId, $assigneeId, $criteria);
    }

	/**
	 * @param array $criteria
	 * @param Queue $queue
	 * @return Criteria[]
	 */
	private function modifyCriteriaByQueue(array $criteria, Queue $queue)
	{
		if ($queue->is(Queue::ALL)){
			return $criteria;
		}

		if ($queue->is(Queue::FRESH)){
			$criteria[] = new Criteria('processStatus', new Constraint(Constraint::IN), [
				new ProcessStatus(ProcessStatus::FRESH),
				new ProcessStatus(ProcessStatus::REQUEST_FOR_BID)
			]);
		} elseif ($queue->is(Queue::ACCEPTED)){
			$criteria[] = new Criteria(
				'processStatus',
				new Constraint(Constraint::EQUAL),
				new ProcessStatus(ProcessStatus::ACCEPTED)
			);
		} elseif ($queue->is(Queue::SCHEDULED)){
			$criteria[] = new Criteria(
				'processStatus',
				new Constraint(Constraint::EQUAL),
				new ProcessStatus(ProcessStatus::INSPECTION_SCHEDULED)
			);
		} elseif ($queue->is(Queue::INSPECTED)){
			$criteria[] = new Criteria(
				'processStatus',
				new Constraint(Constraint::EQUAL),
				new ProcessStatus(ProcessStatus::INSPECTION_COMPLETED)
			);
		} elseif ($queue->is(Queue::ON_HOLD)){
			$criteria[] = new Criteria(
				'processStatus',
				new Constraint(Constraint::EQUAL),
				new ProcessStatus(ProcessStatus::ON_HOLD)
			);
		} elseif ($queue->is(Queue::DUE)){
			$criteria[] = new Criteria('processStatus', new Constraint(Constraint::IN), ProcessStatus::dueToObjects());
		} elseif ($queue->is(Queue::LATE)){
			$criteria[] = new Criteria(
				'processStatus',
				new Constraint(Constraint::EQUAL),
				new ProcessStatus(ProcessStatus::LATE)
			);
		} elseif ($queue->is(Queue::READY_FOR_REVIEW)){
			$criteria[] = new Criteria(
				'processStatus',
				new Constraint(Constraint::EQUAL),
				new ProcessStatus(ProcessStatus::READY_FOR_REVIEW)
			);
		} elseif ($queue->is(Queue::COMPLETED)){
			$criteria[] = new Criteria(
				'processStatus',
				new Constraint(Constraint::EQUAL),
				new ProcessStatus(ProcessStatus::COMPLETED)
			);
		} elseif ($queue->is(Queue::REVISION)){
			$criteria[] = new Criteria('processStatus', new Constraint(Constraint::IN), [
				new ProcessStatus(ProcessStatus::REVISION_IN_REVIEW),
				new ProcessStatus(ProcessStatus::REVISION_PENDING),
			]);
		} elseif ($queue->is(Queue::OPEN)){
			$criteria[] = new Criteria('processStatus', new Constraint(Constraint::IN, true), [
				new ProcessStatus(ProcessStatus::COMPLETED)
			]);
		}

		return $criteria;
	}

	/**
	 * @param int $assigneeId
	 * @return Counter[]
	 */
	public function getCountersByAssigneeId($assigneeId)
	{
		$counters = [];

		foreach (Queue::toObjects() as $queue){
			$counters[] = new Counter($queue, $this->getTotalByAssigneeId($assigneeId, $queue));
		}

		return $counters;
	}

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @return Counter[]
     */
	public function getCountersByCustomerAndAssigneeId($customerId, $assigneeId)
    {
        $counters = [];

        foreach (Queue::toObjects() as $queue){
            $counters[] = new Counter($queue, $this->getTotalByCustomerAndAssigneeIds($customerId, $assigneeId, $queue));
        }

        return $counters;
    }
}
