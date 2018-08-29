<?php
namespace ValuePad\Support\Chance;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AttemptMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('attempts');

        $this->defineId($builder);

        $builder
            ->createField('tag', 'string')
            ->build();

        $builder
            ->createField('data', 'json_array')
            ->build();

        $builder
            ->createField('quantity', 'integer')
            ->build();

        $builder
            ->createField('createdAt', 'datetime')
            ->build();

        $builder
            ->createField('attemptedAt', 'datetime')
            ->nullable(true)
            ->build();

    }
}
