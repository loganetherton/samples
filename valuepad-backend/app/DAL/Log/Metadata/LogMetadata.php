<?php
namespace ValuePad\DAL\Log\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Log\Types\ExtraType;
use ValuePad\DAL\Log\Types\ActionType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class LogMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('logs');

		$this->defineId($builder);

		$builder
			->createManyToOne('order', Order::class)
			->build();

		$builder
			->createManyToOne('user', User::class)
			->build();

		$builder
			->createManyToOne('assignee', User::class)
			->build();

        $builder
            ->createManyToOne('customer', Customer::class)
            ->build();

		$builder
			->createField('action', ActionType::class)
			->build();

		$builder
			->createField('extra', ExtraType::class)
			->build();

		$builder
			->createField('createdAt', 'datetime')
			->build();
	}
}
