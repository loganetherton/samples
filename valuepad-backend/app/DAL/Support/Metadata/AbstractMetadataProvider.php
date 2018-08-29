<?php
namespace ValuePad\DAL\Support\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 *
 *
 */
abstract class AbstractMetadataProvider implements MetadataProviderInterface
{
	const PHONE_LENGTH = 20;
	const EMAIL_LENGTH = 50;
	const MIDDLE_NAME_LENGTH = 50;
	const FIRST_NAME_LENGTH = 50;
	const LAST_NAME_LENGTH = 50;
	const ADDRESS_LENGTH = 100;
	const ZIP_LENGTH = 10;
	const CITY_LENGTH = 100;
	const LICENSE_NUMBER_LENGTH = 50;
	const LATITUDE_LENGTH = 100;
	const LONGITUDE_LENGTH = 100;
    const FILE_NUMBER_LENGTH = 100;
    const LOAN_NUMBER_LENGTH = 100;
    const SECRET_LENGTH = 100;

	/**
	 * @param ClassMetadataBuilder $builder
	 */
	protected function defineId(ClassMetadataBuilder $builder)
	{
		$builder->createField('id', 'integer')
			->makePrimaryKey()
			->generatedValue()
			->build();
	}
}
