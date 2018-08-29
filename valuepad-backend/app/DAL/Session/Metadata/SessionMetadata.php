<?php
namespace ValuePad\DAL\Session\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

/**
 *
 *
 */
class SessionMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('sessions');

		$this->defineId($builder);

        $builder->createField('token', 'string')
            ->unique()
            ->length(100)
            ->build();

        $builder
            ->createManyToOne('user', User::class)
            ->addJoinColumn('user_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder->createField('expireAt', 'datetime')->build();

        $builder->createField('createdAt', 'datetime')->build();
    }
}
