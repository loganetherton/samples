<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class SettingsMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('amc_settings');

        $this->defineId($builder);

        $builder->createOneToOne('amc', Amc::class)
            ->build();

        $builder
            ->createField('pushUrl', 'string')
            ->nullable(true)
            ->build();
    }
}
