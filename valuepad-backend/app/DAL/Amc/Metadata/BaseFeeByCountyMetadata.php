<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Location\Entities\County;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

abstract class BaseFeeByCountyMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $this->defineId($builder);

        $builder
            ->createManyToOne('county', County::class)
            ->build();

        $builder
            ->createField('amount', 'float')
            ->build();
    }
}
