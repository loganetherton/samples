<?php
namespace ValuePad\Core\Customer\Criteria;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\AbstractResolver;

class JobTypeFilterResolver extends AbstractResolver
{
    /**
     * @param QueryBuilder $builder
     * @param bool $flag
     */
    public function whereIsPayableEqual(QueryBuilder $builder, $flag)
    {
        $builder->andWhere($builder->expr()->eq('j.isPayable', ':isPayable'))->setParameter('isPayable', $flag);
    }
}
