<?php
namespace ValuePad\Core\Appraisal\Criteria;

use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Join;
use ValuePad\Core\Support\Criteria\Sorting\AbstractResolver;

class SorterResolver extends AbstractResolver
{
	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join[]
	 */
	public function byClientName(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('cl.name', $direction);

		return [new Join('o.client', 'cl')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join[]
	 */
	public function byCustomerName(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('c.name', $direction);

		return [new Join('o.customer', 'c')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join[]
	 */
	public function byBorrowerName(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('pc.displayName', $direction);

		return [new Join('o.property', 'p'), new Join('p.contacts', 'pc')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byProcessStatus(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.processStatus', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byFileNumber(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.fileNumber', $direction);
	}

    /**
     * @param QueryBuilder $builder
     * @param string $direction
     */
    public function byLoanNumber(QueryBuilder $builder, $direction)
    {
        $builder->addOrderBy('o.loanNumber', $direction);
    }

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byOrderedAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.orderedAt', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byDueDate(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.dueDate', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byAcceptedAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.acceptedAt', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byInspectionScheduledAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.inspectionScheduledAt', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byInspectionCompletedAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.inspectionCompletedAt', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byEstimatedCompletionDate(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.estimatedCompletionDate', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byPutOnHoldAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.putOnHoldAt', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byCompletedAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.completedAt', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 */
	public function byRevisionReceivedAt(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('o.revisionReceivedAt', $direction);
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join
	 */
	public function byPropertyAddress(QueryBuilder $builder, $direction)
	{
		$builder
			->addOrderBy('p.address1', $direction)
			->addOrderBy('p.address2', $direction);

		return new Join('o.property', 'p');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join
	 */
	public function byPropertyStateCode(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('ps.code', $direction);

		return [new Join('o.property', 'p'), new Join('p.state', 'ps')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join
	 */
	public function byPropertyStateName(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('ps.name', $direction);

		return [new Join('o.property', 'p'), new Join('p.state', 'ps')];
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join
	 */
	public function byPropertyCity(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('p.city', $direction);

		return new Join('o.property', 'p');
	}

	/**
	 * @param QueryBuilder $builder
	 * @param string $direction
	 * @return Join
	 */
	public function byPropertyZip(QueryBuilder $builder, $direction)
	{
		$builder->addOrderBy('p.zip', $direction);

		return new Join('o.property', 'p');
	}
}
