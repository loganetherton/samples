<?php
namespace ValuePad\DAL\Customer\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Location\Entities\State;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class ClientMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('clients');

        $this->defineId($builder);

        $builder->createField('name', 'string')
            ->build();

        $builder->createField('address1', 'string')
            ->nullable(true)
            ->build();

        $builder->createField('address2', 'string')
            ->nullable(true)
            ->build();

        $builder->createField('zip', 'string')
            ->nullable(true)
            ->build();

        $builder->createField('city', 'string')
            ->nullable(true)
            ->build();

        $builder->createManyToOne('state', State::class)
            ->addJoinColumn('state', 'code')
            ->build();

        $builder
            ->createManyToOne('customer', Customer::class)
            ->build();
    }
}
