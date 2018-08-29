<?php
namespace ValuePad\DAL\Company\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class StaffMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('staff');

        $this->defineId($builder);

        $builder
            ->createManyToOne('company', Company::class)
            ->build();

        $builder
            ->createManyToOne('branch', Branch::class)
            ->build();

        $builder
            ->createManyToOne('user', User::class)
            ->build();

        $builder
            ->createField('email', 'string')
            ->length(self::EMAIL_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('phone', 'string')
            ->length(self::PHONE_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('isAdmin', 'boolean')
            ->build();

        $builder
            ->createField('isRfpManager', 'boolean')
            ->build();

        $builder
            ->createField('isManager', 'boolean')
            ->build();
    }
}
