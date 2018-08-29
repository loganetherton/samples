<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Entities\Message;
use ValuePad\Core\User\Entities\User;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;
use ValuePad\Core\Customer\Entities\Message as CustomerMessage;

class MessageMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('messages')
			->setSingleTableInheritance()
			->setDiscriminatorColumn('type', 'string', 50)
			->addDiscriminatorMapClass('normal', Message::class)
			->addDiscriminatorMapClass('customer', CustomerMessage::class);

		$this->defineId($builder);

		$builder
			->createField('content', 'text')
			->build();

		$builder
			->createField('createdAt', 'datetime')
			->build();

		$builder
			->createManyToOne('sender', User::class)
			->build();

		$builder
			->createManyToOne('order', Order::class)
			->build();

		$builder
			->createManyToMany('readers', User::class)
			->setJoinTable('message_readers')
			->build();
	}
}
