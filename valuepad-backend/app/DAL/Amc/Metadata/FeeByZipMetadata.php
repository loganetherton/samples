<?php
namespace ValuePad\DAL\Amc\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Fee;

class FeeByZipMetadata extends BaseFeeByZipMetadata
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('amc_zip_fees');

        parent::define($builder);

        $builder
            ->createManyToOne('fee', Fee::class)
            ->build();
    }
}
