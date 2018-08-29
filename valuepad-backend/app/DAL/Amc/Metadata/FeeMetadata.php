<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\JobType\Entities\JobType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class FeeMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('amc_fees');

        $this->defineId($builder);

        $builder
            ->createField('isEnabled', 'boolean')
            ->build();

        $builder
            ->createField('amount', 'float')
            ->build();

        $builder
            ->createManyToOne('jobType', JobType::class)
            ->build();

        $builder
            ->createManyToOne('amc', Amc::class)
            ->build();
    }
}
