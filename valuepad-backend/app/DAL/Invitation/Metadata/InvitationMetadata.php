<?php
namespace ValuePad\DAL\Invitation\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\DAL\Invitation\Types\RequirementsType;
use ValuePad\DAL\Invitation\Types\StatusType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;
use ValuePad\Core\Asc\Entities\AscAppraiser;

class InvitationMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('invitations');

		$this->defineId($builder);

		$builder
			->createField('reference', 'string')
			->build();

		$builder
			->createManyToOne('customer', Customer::class)
			->build();

		$builder
			->createManyToOne('appraiser', Appraiser::class)
			->build();

		$builder
			->createManyToOne('ascAppraiser', AscAppraiser::class)
			->build();

		$builder
			->createField('status', StatusType::class)
			->build();

		$builder
			->createField('createdAt', 'datetime')
			->build();

		$builder->createField('requirements', RequirementsType::class)
			->build();

	}
}
