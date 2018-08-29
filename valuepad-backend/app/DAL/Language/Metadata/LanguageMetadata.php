<?php
namespace ValuePad\DAL\Language\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\MetadataProviderInterface;

/**
 *
 *
 */
class LanguageMetadata implements MetadataProviderInterface
{

    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('languages');

        $builder->createField('code', 'string')
            ->length(3)
            ->makePrimaryKey()
            ->build();

        $builder->createField('name', 'string')
            ->length(100)
            ->build();
    }
}
