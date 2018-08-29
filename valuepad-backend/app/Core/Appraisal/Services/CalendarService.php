<?php
namespace ValuePad\Core\Appraisal\Services;

use ValuePad\Core\Appraisal\Criteria\FilterResolver;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\BadgeType;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Objects\Badge;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Criteria;
use ValuePad\Core\Support\Criteria\Day;
use ValuePad\Core\Support\Criteria\Filter;
use ValuePad\Core\Support\Service\AbstractService;
use DateTime;

class CalendarService extends AbstractService
{
	/**
	 * @param int $assigneeId
	 * @param Day $from
	 * @param Day $to
	 * @return Badge[]
	 */
	public function getAllBadgesWithDayScale($assigneeId, Day $from = null, Day $to = null)
	{
		if ($from === null){
			$from = new Day(date('Y-m-01 00:00:00')); // the 1st day of the current month
		}

		if ($to === null){
			$to = new Day(date('Y-m-t 12:59:59')); // the last day of the current month
		}

		$builder = $this->entityManager->createQueryBuilder();

		$builder
			->select('o')
			->from(Order::class, 'o')
			->where($builder->expr()->eq('o.assignee', ':assignee'))
			->setParameter('assignee', $assigneeId);

		$criteria[] = new Criteria('calendar.range', new Constraint(Constraint::EQUAL), [$from, $to]);

		(new Filter())->apply($builder, $criteria, new FilterResolver());

		/**
		 * @var Order[] $orders
		 */
		$orders = $builder->getQuery()->getResult();

		$badges = [];

		foreach ($orders as $order){
			foreach ($this->getTypes($order) as $type){

				$verifiableDate = $this->getVerifiableDate($type, $order);

				if (($verifiableDate < $from->startsAt() || $verifiableDate > $to->endsAt())){
					continue ;
				}

				$position = $this->getPosition($order, $type);

				$group = $type.'-'.implode('-', $position);

				if (!isset($badges[$group])){
					$badge = new Badge();

					$badge->setPosition($position);
					$badge->setType($type);

					$badges[$group] = $badge;
				} else {
					$badge = $badges[$group];
				}

				$badge->increaseCounter();
				$badge->addOrder($order);
			}
		}

		return $badges;
	}

	/**
	 * @param BadgeType $type
	 * @param Order $order
	 * @return DateTime
	 */
	private function getVerifiableDate(BadgeType $type, Order $order)
	{
		if ($type->is([BadgeType::FRESH, BadgeType::REQUEST_FOR_BID])){
			return $order->getOrderedAt();
		}

		if ($type->is(BadgeType::INSPECTION_SCHEDULED)){
			return $order->getInspectionScheduledAt();
		}

		return $order->getDueDate();
	}

	/**
	 * @param Order $order
	 * @return BadgeType[]
	 */
	private function getTypes(Order $order)
	{
		$types = [];

		if ($order->getProcessStatus()->is(ProcessStatus::FRESH)){
			$types[] = new BadgeType(BadgeType::FRESH);
		}

		if ($order->getProcessStatus()->is(ProcessStatus::REQUEST_FOR_BID)){
			$types[] = new BadgeType(BadgeType::REQUEST_FOR_BID);
		}

		if ($order->getProcessStatus()->is(ProcessStatus::INSPECTION_SCHEDULED)){
			$types[] = new BadgeType(BadgeType::INSPECTION_SCHEDULED);
		}

		if ($order->getDueDate() !== null){
			$types[] = new BadgeType(BadgeType::DUE);
		}

		return $types;
	}

	/**
	 * @param Order $order
	 * @param BadgeType $type
	 * @return array
	 */
	private function getPosition(Order $order, BadgeType $type)
	{
		$occurredAt = $this->getVerifiableDate($type, $order);

		return [
			(int) $occurredAt->format('Y'),
			(int) $occurredAt->format('m'),
			(int) $occurredAt->format('d')
		];
	}
}
