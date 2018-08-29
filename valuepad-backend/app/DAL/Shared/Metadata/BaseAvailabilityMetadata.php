<?php
namespace ValuePad\DAL\Shared\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

abstract class BaseAvailabilityMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $this->defineId($builder);

        $builder
            ->createField('isOnVacation', 'boolean')
            ->build();

        $builder
            ->createField('from', 'datetime')
            ->columnName('`from`')
            ->nullable(true)
            ->build();

        $builder
            ->createField('to', 'datetime')
            ->columnName('`to`')
            ->nullable(true)
            ->build();

        $builder
            ->createField('message', 'text')
            ->nullable(true)
            ->build();
    }
}
