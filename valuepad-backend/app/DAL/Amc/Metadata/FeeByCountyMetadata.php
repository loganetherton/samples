<?php
namespace ValuePad\DAL\Amc\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Fee;

class FeeByCountyMetadata extends BaseFeeByCountyMetadata
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('amc_county_fees');

        parent::define($builder);

        $builder
            ->createManyToOne('fee', Fee::class)
            ->build();
    }
}
