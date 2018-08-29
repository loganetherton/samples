<?php
namespace ValuePad\Letter\Support;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class FrequencyMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('emails_frequency_tracker');

        $this->defineId($builder);

        $builder
            ->createManyToOne('order', Order::class)
            ->addJoinColumn('order_id', 'id', true, false, 'CASCADE')
            ->build();


        $builder
            ->createField('alias', 'string')
            ->build();

        $builder
            ->createField('updatedAt', 'datetime')
            ->build();

    }
}
