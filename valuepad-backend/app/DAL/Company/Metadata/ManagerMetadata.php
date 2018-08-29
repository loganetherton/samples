<?php
namespace ValuePad\DAL\Company\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Shared\Entities\Availability;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class ManagerMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder
            ->createField('firstName', 'string')
            ->length(self::FIRST_NAME_LENGTH)
            ->build();

        $builder
            ->createField('lastName', 'string')
            ->length(self::LAST_NAME_LENGTH)
            ->build();

        $builder
            ->createField('phone', 'string')
            ->length(self::PHONE_LENGTH)
            ->build();

        $builder
            ->createOneToOne('staff', Staff::class)
            ->mappedBy('user')
            ->build();

        $builder
            ->createOneToOne('availability', Availability::class)
            ->cascadeRemove()
            ->build();

        $builder
            ->createManyToMany('customers', Customer::class)
            ->mappedBy('managers')
            ->build();
    }
}
