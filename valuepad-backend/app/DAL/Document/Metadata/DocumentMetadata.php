<?php
namespace ValuePad\DAL\Document\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Document\Types\FormatType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class DocumentMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('documents');

        $this->defineId($builder);

        $builder->createField('token', 'string')
            ->unique(true)
            ->length(100)
            ->build();

        $builder->createField('usage', 'integer')
            ->columnName('`usage`')
            ->build();

        $builder->createField('name', 'string')->build();
        $builder->createField('uri', 'string')->build();
        $builder->createField('uploadedAt', 'datetime')->build();
        $builder->createField('format', FormatType::class)->build();
        $builder->createField('size', 'integer')->build();
        $builder->createField('isExternal', 'boolean')->build();
	}
}
