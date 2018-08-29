<?php
namespace ValuePad\Core\Support\Criteria;

use Doctrine\ORM\QueryBuilder;
/**
 *
 *
 */
interface ResolverInterface
{
    /**
     * @param Criteria $criteria
     * @return bool
     */
    public function canResolve(Criteria $criteria);

    /**
     * @param QueryBuilder $builder
     * @param Criteria $criteria
     * @return Join|null
     */
    public function resolve(QueryBuilder $builder, Criteria $criteria);
}
