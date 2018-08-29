<?php
namespace ValuePad\DAL\Company\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Company\Entities\Company;
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
        $builder->setTable('company_fees');

        $this->defineId($builder);

        $builder
            ->createField('amount', 'float')
            ->build();

        $builder
            ->createManyToOne('jobType', JobType::class)
            ->addJoinColumn('job_type_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder
            ->createManyToOne('company', Company::class)
            ->addJoinColumn('company_id', 'id', true, false, 'CASCADE')
            ->build();
    }
}
