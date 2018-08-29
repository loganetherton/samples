<?php
namespace ValuePad\DAL\User\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Back\Entities\Admin;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\User\Entities\System;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;
use ValuePad\DAL\User\Types\StatusType;

class UserMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('users')
            ->setSingleTableInheritance()
            ->setDiscriminatorColumn('type', 'string', 20)
            ->addDiscriminatorMapClass('system', System::class)
            ->addDiscriminatorMapClass('admin', Admin::class)
            ->addDiscriminatorMapClass('amc', Amc::class)
            ->addDiscriminatorMapClass('appraiser', Appraiser::class)
			->addDiscriminatorMapClass('customer', Customer::class)
            ->addDiscriminatorMapClass('manager', Manager::class);

		$this->defineId($builder);

        $builder
			->createField('username', 'string')
			->build();

        $builder->createField('password', 'string')
            ->build();

        $builder
            ->createField('email', 'string')
            ->length(static::EMAIL_LENGTH)
            ->nullable(true)
            ->build();

        $builder
            ->createField('createdAt', 'datetime')
            ->nullable(true)
            ->build();


        $builder
            ->createField('updatedAt', 'datetime')
            ->nullable(true)
            ->build();

        $builder
            ->createField('status', StatusType::class)
            ->build();
    }
}
