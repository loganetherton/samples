<?php
namespace ValuePad\DAL\Back\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AdminMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->createField('firstName', 'string')
            ->length(static::FIRST_NAME_LENGTH)
            ->build();

        $builder->createField('lastName', 'string')
            ->length(static::LAST_NAME_LENGTH)
            ->build();
    }
}
