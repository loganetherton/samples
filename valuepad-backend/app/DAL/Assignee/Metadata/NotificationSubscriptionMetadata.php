<?php
namespace ValuePad\DAL\Assignee\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class NotificationSubscriptionMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('notification_subscriptions');

		$this->defineId($builder);

		$builder
			->createManyToOne('assignee', User::class)
			->build();

		$builder
			->createManyToOne('customer', Customer::class)
			->build();

		$builder
			->createField('email', 'boolean')
			->build();
	}
}
