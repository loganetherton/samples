<?php
namespace ValuePad\DAL\Shared\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;

class AvailabilityPerCustomerMetadata extends BaseAvailabilityMetadata
{
    /**
     * @param ClassMetadataBuilder $builder
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('availabilities_per_customer');
        parent::define($builder);

        $builder
            ->createManyToOne('customer', Customer::class)
            ->inversedBy('customer')
            ->build();

        $builder
            ->createManyToOne('user', User::class)
            ->inversedBy('user')
            ->build();
    }
}
