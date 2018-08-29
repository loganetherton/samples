<?php
namespace ValuePad\Core\Appraisal\Criteria;

use Ascope\Libraries\Validation\Rules\Moment;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Appraisal\Enums\Due;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Enums\Property\ContactType;
use ValuePad\Core\Support\Criteria\AbstractResolver;
use ValuePad\Core\Support\Criteria\Day;
use ValuePad\Core\Support\Criteria\Join;
use DateTime;

class FilterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 * @return Join[]
	 */
	public function whereClientNameSimilar(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->like('cl.name', ':clientName'))
			->setParameter('clientName', '%' . addcslashes($value, '%_') . '%');

		return [new Join('o.client', 'cl')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param $value
	 * @return Join[]
	 */
	public function whereCustomerNameSimilar(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->like('c.name', ':customerName'))
			->setParameter('customerName', '%' . addcslashes($value, '%_') . '%');

		return [new Join('o.customer', 'c')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 * @return Join[]
	 */
	public function whereBorrowerNameSimilar(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->like('pc.displayName', ':propertyContactBorrowerDisplayName'))
			->setParameter('propertyContactBorrowerDisplayName', '%' . addcslashes($value, '%_') . '%');

		$builder
			->andWhere($builder->expr()->eq('pc.type', ':propertyContactType'))
			->setParameter('propertyContactType', new ContactType(ContactType::BORROWER));


		return [new Join('o.property', 'p'), new Join('p.contacts', 'pc')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 * @return Join
	 */
	public function wherePropertyCitySimilar(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->like('p.city', ':propertyCity'))
			->setParameter('propertyCity', '%' . addcslashes($value, '%_') . '%');

		return new Join('o.property', 'p');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 * @return Join
	 */
	public function wherePropertyAddressSimilar(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->orX(
			$builder->expr()->like('p.address1', ':propertyAddress1'),
			$builder->expr()->like('p.address2', ':propertyAddress2')
		))
			->setParameter('propertyAddress1', '%' . addcslashes($value, '%_') . '%')
			->setParameter('propertyAddress2', '%' . addcslashes($value, '%_') . '%');

		return new Join('o.property', 'p');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param ProcessStatus[] $statuses
	 * @return void
	 */
	public function whereProcessStatusIn(QueryBuilder $builder, array $statuses)
	{
		$builder->andWhere($builder->expr()->in('o.processStatus', ':processStatuses'))
			->setParameter('processStatuses', array_map(function(ProcessStatus $status){
				return (string) $status;
			}, $statuses));
	}

	/**
	 * @param QueryBuilder $builder
	 * @param ProcessStatus[] $statuses
	 * @return void
	 */
	public function whereProcessStatusNotIn(QueryBuilder $builder, array $statuses)
	{
		$builder->andWhere($builder->expr()->notIn('o.processStatus', ':notProcessStatuses'))
			->setParameter('notProcessStatuses', array_map(function(ProcessStatus $status){
				return (string) $status;
			}, $statuses));
	}

	/**
	 * @param QueryBuilder $builder
	 * @param ProcessStatus $status
	 * @return void
	 */
	public function whereProcessStatusEqual(QueryBuilder $builder, ProcessStatus $status)
	{
		$builder
			->andWhere($builder->expr()->eq('o.processStatus', ':processStatus'))
			->setParameter('processStatus', $status->value());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 */
	public function whereFileNumberEqual(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->eq('o.fileNumber', ':eFileNumber'))
			->setParameter('eFileNumber', $value);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 */
	public function whereFileNumberSimilar(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->like('o.fileNumber', ':sFileNumber'))
			->setParameter('sFileNumber', '%' . addcslashes($value, '%_') . '%');
	}

    /**
     * @param QueryBuilder $builder
     * @param string $value
     */
    public function whereLoanNumberSimilar(QueryBuilder $builder, $value)
    {
        $builder->andWhere($builder->expr()->like('o.loanNumber', ':sLoanNumber'))
            ->setParameter('sLoanNumber', '%' . addcslashes($value, '%_') . '%');
    }

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 * @return Join
	 */
	public function wherePropertyStateEqual(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->eq('p.state', ':propertyState'))
			->setParameter('propertyState', $value);

		return new Join('o.property', 'p');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param $value
	 * @return Join
	 */
	public function wherePropertyZipEqual(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->eq('p.zip', ':ePropertyZip'))
			->setParameter('ePropertyZip', $value);

		return new Join('o.property', 'p');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param $value
	 * @return Join
	 */
	public function wherePropertyZipSimilar(QueryBuilder $builder, $value)
	{
		$builder->andWhere($builder->expr()->like('p.zip', ':sPropertyZip'))
			->setParameter('sPropertyZip', '%' . addcslashes($value, '%_') . '%');

		return new Join('o.property', 'p');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereOrderedAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between('o.orderedAt', ':startOrderedAt', ':endOrderedAt'))
			->setParameter('startOrderedAt', $day->startsAt())
			->setParameter('endOrderedAt', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereOrderedAtDayEqual(QueryBuilder $builder, Day $day)
	{
		$this->whereOrderedAtEqual($builder, $day);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereOrderedAtFromGreaterOrEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->gte('o.orderedAt', ':orderedAtFrom'))
			->setParameter('orderedAtFrom', $day->startsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereOrderedAtToLessOrEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->lte('o.orderedAt', ':orderedAtTo'))
			->setParameter('orderedAtTo', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $year
	 */
	public function whereOrderedAtYearEqual(QueryBuilder $builder, $year)
	{
		$builder
			->andWhere($builder->expr()->eq('YEAR(o.orderedAt)', ':orderedAtYear'))
			->setParameter('orderedAtYear', (string) $year);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $month
	 */
	public function whereOrderedAtMonthEqual(QueryBuilder $builder, $month)
	{
		$builder
			->andWhere($builder->expr()->eq('MONTH(o.orderedAt)', ':orderedAtMonth'))
			->setParameter('orderedAtMonth', str_pad($month, 2, '0', STR_PAD_LEFT));
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereDueDateEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between('o.dueDate', ':startDueDate', ':endDueDate'))
			->setParameter('startDueDate', $day->startsAt())
			->setParameter('endDueDate', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Due $due
	 */
	public function whereDueEqual(QueryBuilder $builder, Due $due)
	{
		$config = [
			Due::TODAY => (new Day(new DateTime()))->endsAt(),
			Due::TOMORROW => (new Day(new DateTime('+1 day')))->endsAt(),
			Due::NEXT_7_DAYS => (new Day(new DateTime('+7 days')))->endsAt(),
		];

		$today = (new Day(new DateTime()))->startsAt();
		$future = $config[$due->value()];

		$builder
			->andWhere($builder->expr()->between('o.dueDate', ':startDue', ':endDue'))
			->setParameter('startDue', $today)
			->setParameter('endDue', $future);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereAcceptedAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between('o.acceptedAt', ':startAcceptedAt', ':endAcceptedAt'))
			->setParameter('startAcceptedAt', $day->startsAt())
			->setParameter('endAcceptedAt', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereInspectionScheduledAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between(
				'o.inspectionScheduledAt',
				':startInspectionScheduledAt',
				':endInspectionScheduledAt'
			))
			->setParameter('startInspectionScheduledAt', $day->startsAt())
			->setParameter('endInspectionScheduledAt', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereInspectionCompletedAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between(
				'o.inspectionCompletedAt',
				':startInspectionCompletedAt',
				':endInspectionCompletedAt'
			))
			->setParameter('startInspectionCompletedAt', $day->startsAt())
			->setParameter('endInspectionCompletedAt', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereEstimatedCompletionDateEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between(
				'o.estimatedCompletionDate',
				':startEstimatedCompletionDate',
				':endEstimatedCompletionDate'
			))
			->setParameter('startEstimatedCompletionDate', $day->startsAt())
			->setParameter('endEstimatedCompletionDate', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function wherePutOnHoldAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between(
				'o.putOnHoldAt',
				':startPutOnHoldAt',
				':endPutOnHoldAt'
			))
			->setParameter('startPutOnHoldAt', $day->startsAt())
			->setParameter('endPutOnHoldAt', $day->endsAt());
	}


	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereCompletedAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between(
				'o.completedAt',
				':startCompletedAt',
				':endCompletedAt'
			))
			->setParameter('startCompletedAt', $day->startsAt())
			->setParameter('endCompletedAt', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereCompletedAtDayEqual(QueryBuilder $builder, Day $day)
	{
		$this->whereCompletedAtEqual($builder, $day);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereCompletedAtFromGreaterOrEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->gte('o.completedAt', ':completedAtFrom'))
			->setParameter('completedAtFrom', $day->startsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereCompletedAtToLessOrEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->lte('o.completedAt', ':completedAtTo'))
			->setParameter('completedAtTo', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $year
	 */
	public function whereCompletedAtYearEqual(QueryBuilder $builder, $year)
	{
		$builder
			->andWhere($builder->expr()->eq('YEAR(o.completedAt)', ':completedAtYear'))
			->setParameter('completedAtYear', (string) $year);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $month
	 */
	public function whereCompletedAtMonthEqual(QueryBuilder $builder, $month)
	{
		$builder
			->andWhere($builder->expr()->eq('MONTH(o.completedAt)', ':completedAtMonth'))
			->setParameter('completedAtMonth', str_pad($month, 2, '0', STR_PAD_LEFT));
	}


	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function wherePaidAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between(
				'o.paidAt',
				':startPaidAt',
				':endPaidAt'
			))
			->setParameter('startPaidAt', $day->startsAt())
			->setParameter('endPaidAt', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function wherePaidAtDayEqual(QueryBuilder $builder, Day $day)
	{
		$this->wherePaidAtEqual($builder, $day);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function wherePaidAtFromGreaterOrEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->gte('o.paidAt', ':paidAtFrom'))
			->setParameter('paidAtFrom', $day->startsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function wherePaidAtToLessOrEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->lte('o.paidAt', ':paidAtTo'))
			->setParameter('paidAtTo', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $year
	 */
	public function wherePaidAtYearEqual(QueryBuilder $builder, $year)
	{
		$builder
			->andWhere($builder->expr()->eq('YEAR(o.paidAt)', ':paidAtYear'))
			->setParameter('paidAtYear', (string) $year);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param int $month
	 */
	public function wherePaidAtMonthEqual(QueryBuilder $builder, $month)
	{
		$builder
			->andWhere($builder->expr()->eq('MONTH(o.paidAt)', ':paidAtMonth'))
			->setParameter('paidAtMonth', str_pad($month, 2, '0', STR_PAD_LEFT));
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day $day
	 */
	public function whereRevisionReceivedAtEqual(QueryBuilder $builder, Day $day)
	{
		$builder
			->andWhere($builder->expr()->between(
				'o.revisionReceivedAt',
				':startRevisionReceivedAt',
				':endRevisionReceivedAt'
			))
			->setParameter('startRevisionReceivedAt', $day->startsAt())
			->setParameter('endRevisionReceivedAt', $day->endsAt());
	}

	/**
	 * @param QueryBuilder $builder
	 * @param bool $flag
	 */
	public function whereIsPaidEqual(QueryBuilder $builder, $flag)
	{
		$builder
			->andWhere($builder->expr()->eq('o.isPaid', ':isPaid'))
			->setParameter('isPaid', $flag);
	}

    /**
     * @param QueryBuilder $builder
     * @param int $id
     * @return Join[]
     */
	public function whereCompanyEqual(QueryBuilder $builder, $id)
    {
        $builder
            ->andWhere($builder->expr()->eq('s.company', ':staffCompany'))
            ->setParameter('staffCompany', $id);

        return new Join('o.staff', 's');
    }

	/**
	 * @param QueryBuilder $builder
	 * @param string $value
	 * @return bool
	 */
	public function whereQuerySimilar(QueryBuilder $builder, $value)
	{
		$fileNumber =
		$loanNumber =
		$customerName =
		$borrowerName =
		$clientName =
		$address =
		$zip =
		$stateName =
		$city = '%' . addcslashes($value, '%_') . '%';

		$stateCode = $value;
		$qBorrowerType = ContactType::BORROWER;

		$q = 'o.fileNumber LIKE :qFileNumber
		    OR o.loanNumber LIKE :qLoanNumber
			OR (pc.displayName LIKE :qBorrowerName AND pc.type = :qBorrowerType)
			OR c.name LIKE :qCustomerName
			OR cl.name LIKE :qClientName
			OR p.address1 LIKE :qPropertyAddress1
			OR p.address2 LIKE :qPropertyAddress2
			OR p.zip LIKE :qPropertyZip
			OR p.city LIKE :qPropertyCity
			OR ps.name LIKE :qStateName
			OR p.state = :qStateCode';

		$parameters = [
			'qFileNumber' => $fileNumber,
			'qLoanNumber' => $loanNumber,
			'qCustomerName' => $customerName,
			'qBorrowerName' => $borrowerName,
			'qClientName' => $clientName,
			'qBorrowerType' => $qBorrowerType,
			'qPropertyAddress1' => $address,
			'qPropertyAddress2' => $address,
			'qPropertyZip' => $zip,
			'qPropertyCity' => $city,
			'qStateName' => $stateName,
			'qStateCode' => $stateCode
		];

		if ((new Moment('Y-m-d'))->check($value) === null
			|| (new Moment('m/d/Y'))->check($value) === null
			|| (new Moment('m/d/y'))->check($value) === null
			|| (new Moment('d-m-Y'))->check($value) === null
			|| (new Moment('d-m-y'))->check($value) === null){
			$day = new Day($value);

			$q .= ' OR (o.orderedAt BETWEEN :qStartOrderedAt AND :qEndOrderedAt)';
			$parameters['qStartOrderedAt'] = $day->startsAt();
			$parameters['qEndOrderedAt'] = $day->endsAt();

			$q .= ' OR (o.dueDate BETWEEN :qStartDueDate AND :qEndDueDate)';
			$parameters['qStartDueDate'] = $day->startsAt();
			$parameters['qEndDueDate'] = $day->endsAt();

			$q .= ' OR (o.acceptedAt BETWEEN :qStartAcceptedAt AND :qEndAcceptedAt)';
			$parameters['qStartAcceptedAt'] = $day->startsAt();
			$parameters['qEndAcceptedAt'] = $day->endsAt();

			$q .= ' OR (o.inspectionCompletedAt BETWEEN :qStartInspectionCompletedAt AND :qEndInspectionCompletedAt)';
			$parameters['qStartInspectionCompletedAt'] = $day->startsAt();
			$parameters['qEndInspectionCompletedAt'] = $day->endsAt();

			$q .= ' OR (o.inspectionScheduledAt BETWEEN :qStartInspectionScheduledAt AND :qEndInspectionScheduledAt)';
			$parameters['qStartInspectionScheduledAt'] = $day->startsAt();
			$parameters['qEndInspectionScheduledAt'] = $day->endsAt();

			$q .= ' OR (o.estimatedCompletionDate BETWEEN :qStartEstimatedCompletionDate AND :qEndEstimatedCompletionDate)';
			$parameters['qStartEstimatedCompletionDate'] = $day->startsAt();
			$parameters['qEndEstimatedCompletionDate'] = $day->endsAt();

			$q .= ' OR (o.putOnHoldAt BETWEEN :qStartPutOnHoldAt AND :qEndPutOnHoldAt)';
			$parameters['qStartPutOnHoldAt'] = $day->startsAt();
			$parameters['qEndPutOnHoldAt'] = $day->endsAt();

			$q .= ' OR (o.completedAt BETWEEN :qStartCompletedAt AND :qEndCompletedAt)';
			$parameters['qStartCompletedAt'] = $day->startsAt();
			$parameters['qEndCompletedAt'] = $day->endsAt();

			$q .= ' OR (o.paidAt BETWEEN :qStartPaidAt AND :qEndPaidAt)';
			$parameters['qStartPaidAt'] = $day->startsAt();
			$parameters['qEndPaidAt'] = $day->endsAt();

			$q .= ' OR (o.revisionReceivedAt BETWEEN :qStartRevisionReceivedAt AND :qEndRevisionReceivedAt)';
			$parameters['qStartRevisionReceivedAt'] = $day->startsAt();
			$parameters['qEndRevisionReceivedAt'] = $day->endsAt();
		}

		$builder->andWhere($q);

		foreach ($parameters as $name => $value){
			$builder->setParameter($name, $value);
		}

		return [
			new Join('o.property', 'p'),
			new Join('p.contacts', 'pc'),
			new Join('p.state', 'ps'),
			new Join('o.customer', 'c'),
			new Join('o.client', 'cl')
		];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param Day[] $range
	 */
	public function whereCalendarRangeEqual(QueryBuilder $builder, array $range)
	{
		/**
		 * @var Day $from
		 * @var Day $to
		 */
		list($from, $to) = $range;

		$query = '((o.processStatus IN (:calendarProcessStatuses) AND o.orderedAt >= :calendarFromDate AND o.orderedAt <= :calendarToDate)';
		$query .= 'OR (o.processStatus=:calendarProcessStatus AND o.inspectionScheduledAt >= :calendarFromDate AND o.inspectionScheduledAt <= :calendarToDate)';
		$query .= 'OR (o.dueDate IS NOT NULL AND o.dueDate >= :calendarFromDate AND o.dueDate <= :calendarToDate))';

		$builder->andWhere($query)
			->setParameter('calendarProcessStatus', ProcessStatus::INSPECTION_SCHEDULED)
			->setParameter('calendarProcessStatuses', [ProcessStatus::FRESH, ProcessStatus::REQUEST_FOR_BID])
			->setParameter('calendarFromDate', $from->startsAt())
			->setParameter('calendarToDate', $to->endsAt());
	}

}
