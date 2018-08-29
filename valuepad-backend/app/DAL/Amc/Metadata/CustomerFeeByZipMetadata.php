<?php
namespace ValuePad\DAL\Amc\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeByZipMetadata extends BaseFeeByZipMetadata
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('amc_customer_zip_fees');

        parent::define($builder);

        $builder
            ->createManyToOne('fee', CustomerFee::class)
            ->build();
    }
}
