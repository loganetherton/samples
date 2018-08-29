<?php
namespace ValuePad\Core\Amc\Criteria;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\AbstractResolver;

class InvoiceFilterResolver extends AbstractResolver
{
    /**
     * @param QueryBuilder $builder
     * @param bool $flag
     */
    public function whereIsPaidEqual(QueryBuilder $builder, $flag)
    {
        $builder
            ->andWhere($builder->expr()->eq('i.isPaid', ':isPaid'))
            ->setParameter('isPaid', $flag);
    }
}
