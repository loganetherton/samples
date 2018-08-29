<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Location\Entities\State;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

abstract class BaseFeeByStateMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $this->defineId($builder);

        $builder
            ->createManyToOne('state', State::class)
            ->addJoinColumn('state', 'code')
            ->build();

        $builder
            ->createField('amount', 'float')
            ->build();
    }
}
