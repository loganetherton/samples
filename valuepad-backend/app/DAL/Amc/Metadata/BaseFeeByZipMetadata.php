<?php
namespace ValuePad\DAL\Amc\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

abstract class BaseFeeByZipMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $this->defineId($builder);

        $builder
            ->createField('zip', 'string')
            ->length(static::ZIP_LENGTH)
            ->build();

        $builder
            ->createField('amount', 'float')
            ->build();
    }
}
