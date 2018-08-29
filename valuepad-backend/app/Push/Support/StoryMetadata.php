<?php
namespace ValuePad\Push\Support;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class StoryMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('request_logs');
        $this->defineId($builder);

        $builder
            ->createField('request', 'json_array')
            ->build();

        $builder
            ->createField('response', 'json_array')
            ->nullable(true)
            ->build();

        $builder
            ->createField('error', 'json_array')
            ->nullable(true)
            ->build();

        $builder
            ->createField('createdAt', 'datetime')
            ->build();

        $builder
            ->createField('code', 'integer')
            ->build();

        // Setting this one up without using association because orders
        // might get deleted occasionally.
        $builder
            ->createField('order_id', 'integer')
            ->nullable(true)
            ->build();

        $builder
            ->createManyToOne('sender', User::class)
            ->build();

        $builder
            ->createManyToOne('recipient', User::class)
            ->build();

        $builder
            ->createField('type', 'string')
            ->build();

        $builder
            ->createField('event', 'string')
            ->build();

        $builder->addIndex(['order_id'], 'index_story_order');
        $builder->addIndex(['type'], 'index_story_type');
        $builder->addIndex(['event'], 'index_story_event');
    }
}
