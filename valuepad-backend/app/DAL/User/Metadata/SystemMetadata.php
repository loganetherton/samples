<?php
namespace ValuePad\DAL\User\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class SystemMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->createField('name', 'string')
            ->length(100)
            ->build();
    }
}
