<?php
namespace ValuePad\Core\Company\Criteria;
use Doctrine\ORM\QueryBuilder;
use ValuePad\Core\Support\Criteria\AbstractResolver;
use ValuePad\Core\Support\Criteria\Join;

class StaffFilterResolver extends AbstractResolver
{
    /**
     * @param QueryBuilder $builder
     * @param string $class
     * @return Join[]
     */
    public function whereUserClassEqual(QueryBuilder $builder, $class)
    {
        $builder->andWhere($builder->expr()->isInstanceOf('u', $class));
        return [new Join('s.user', 'u')];
    }
}
