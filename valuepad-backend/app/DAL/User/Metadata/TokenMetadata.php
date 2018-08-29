<?php
namespace ValuePad\DAL\User\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;
use ValuePad\DAL\User\Types\IntentType;

class TokenMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('tokens');

		$this->defineId($builder);

		$builder
			->createManyToOne('user', User::class)
            ->addJoinColumn('user_id', 'id', true, false, 'CASCADE')
			->build();

		$builder
			->createField('value', 'string')
			->unique(true)
			->length(100)
			->build();

		$builder
			->createField('intent', IntentType::class)
			->build();

		$builder
			->createField('createdAt', 'datetime')
			->build();

		$builder
			->createField('expiresAt', 'datetime')
			->build();
	}
}
