<?php
namespace ValuePad\DAL\Appraisal\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Appraisal\Types\AssetTypeType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class FdicMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('fdic');

        $this->defineId($builder);

        $builder
            ->createField('fin', 'string')
            ->length(5)
            ->nullable(true)
            ->build();

        $builder
            ->createField('taskOrder', 'string')
            ->length(4)
            ->nullable(true)
            ->build();

        $builder
            ->createField('assetNumber', 'string')
            ->length(12)
            ->nullable(true)
            ->build();

        $builder
            ->createField('assetType', AssetTypeType::class)
            ->nullable(true)
            ->build();

        $builder
            ->createField('line', 'smallint')
            ->nullable(true)
            ->build();

        $builder
            ->createField('contractor', 'string')
            ->length(30)
            ->nullable(true)
            ->build();
    }
}
