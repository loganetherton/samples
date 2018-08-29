<?php
namespace ValuePad\DAL\User\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;
use ValuePad\DAL\User\Types\PlatformType;

class DeviceMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('devices');
		$this->defineId($builder);

		$builder
			->createField('token', 'string')
			->build();

		$builder
			->createManyToOne('user', User::class)
            ->addJoinColumn('user_id', 'id', true, false, 'CASCADE')
			->build();

		$builder
			->createField('platform', PlatformType::class)
			->build();
	}
}
