<?php
namespace ValuePad\DAL\Appraisal\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraisal\Entities\Property;
use ValuePad\DAL\Appraisal\Types\ContactTypeType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class ContactMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('contacts');

		$this->defineId($builder);

		$builder->createField('firstName', 'string')
			->length(self::FIRST_NAME_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('lastName', 'string')
			->length(self::LAST_NAME_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('displayName', 'string')
			->nullable(true)
			->build();

		$builder->createField('middleName', 'string')
			->length(self::MIDDLE_NAME_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('homePhone', 'string')
			->length(self::PHONE_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('workPhone', 'string')
			->length(self::PHONE_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('cellPhone', 'string')
			->length(self::PHONE_LENGTH)
			->nullable(true)
			->build();

		$builder
			->createField('type', ContactTypeType::class)
			->build();

		$builder
			->createManyToOne('property', Property::class)
			->inversedBy('contacts')
			->build();

		$builder
			->createField('name', 'string')
			->nullable(true)
			->build();

		$builder
			->createField('email', 'string')
			->nullable(true)
			->build();

        $builder
            ->createField('intentProceedDate', 'datetime')
            ->nullable(true)
            ->build();
	}
}
