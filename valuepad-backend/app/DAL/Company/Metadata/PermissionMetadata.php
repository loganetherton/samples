<?php
namespace ValuePad\DAL\Company\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class PermissionMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('permissions');

        $this->defineId($builder);

        $builder
            ->createManyToOne('manager', Staff::class)
            ->addJoinColumn('manager_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder
            ->createManyToOne('appraiser', Staff::class)
            ->addJoinColumn('appraiser_id', 'id', true, false, 'CASCADE')
            ->build();
    }
}
