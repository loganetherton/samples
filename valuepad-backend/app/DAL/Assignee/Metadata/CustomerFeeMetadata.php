<?php
namespace ValuePad\DAL\Assignee\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class CustomerFeeMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('customer_fees');

        $this->defineId($builder);

        $builder
            ->createField('amount', 'float')
            ->build();

        $builder
            ->createManyToOne('jobType', JobType::class)
            ->build();

        $builder
            ->createManyToOne('customer', Customer::class)
            ->build();

        $builder
            ->createManyToOne('assignee', User::class)
            ->build();
    }
}
