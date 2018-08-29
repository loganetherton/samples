<?php
namespace ValuePad\DAL\Support\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

interface MetadataProviderInterface
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder);
}
