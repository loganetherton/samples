<?php
namespace ValuePad\DAL\Location\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\MetadataProviderInterface;

/**
 *
 *
 */
class StateMetadata implements MetadataProviderInterface
{

    /**
     *
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('states');

        $builder->createField('code', 'string')
            ->makePrimaryKey()
            ->length(3)
            ->build();

        $builder->createField('name', 'string')
            ->length(50)
            ->build();
    }
}
