<?php
namespace ValuePad\DAL\Appraisal\Metadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class SupportingDetailsMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('order_supporting_details');

        $this->defineId($builder);

        $builder->createOneToOne('order', Order::class)
            ->addJoinColumn('order_id', 'id', true, false, 'CASCADE')
            ->inversedBy('supportingDetails')
            ->build();

        $builder
            ->createField('unacceptedRemindedAt', 'datetime')
            ->nullable(true)
            ->build();
    }
}
