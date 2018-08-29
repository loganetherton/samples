<?php
namespace ValuePad\Core\Amc\Criteria;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\Sorting\AbstractResolver;

class InvoiceSorterResolver extends AbstractResolver
{
    /**
     * @param QueryBuilder $builder
     * @param string $direction
     */
    public function byCreatedAt(QueryBuilder $builder, $direction)
    {
        $builder->addOrderBy('i.createdAt', $direction);
    }

    /**
     * @param QueryBuilder $builder
     * @param string $direction
     */
    public function byFrom(QueryBuilder $builder, $direction)
    {
        $builder->addOrderBy('i.from', $direction);
    }

    /**
     * @param QueryBuilder $builder
     * @param string $direction
     */
    public function byTo(QueryBuilder $builder, $direction)
    {
        $builder->addOrderBy('i.from', $direction);
    }

    /**
     * @param QueryBuilder $builder
     * @param string $direction
     */
    public function byAmount(QueryBuilder $builder, $direction)
    {
        $builder->addOrderBy('i.amount', $direction);
    }

    /**
     * @param QueryBuilder $builder
     * @param string $direction
     */
    public function byIsPaid(QueryBuilder $builder, $direction)
    {
        $builder->addOrderBy('i.isPaid', $direction);
    }
}
