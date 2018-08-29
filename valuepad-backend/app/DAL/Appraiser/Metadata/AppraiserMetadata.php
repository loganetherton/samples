<?php
namespace ValuePad\DAL\Appraiser\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\Ach;
use ValuePad\Core\Shared\Entities\Availability;
use ValuePad\Core\Appraiser\Entities\DefaultFee;
use ValuePad\Core\Appraiser\Entities\EoEx;
use ValuePad\Core\Appraiser\Entities\License;
use ValuePad\Core\Appraiser\Entities\Qualifications;
use ValuePad\Core\Asc\Entities\AscAppraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Language\Entities\Language;
use ValuePad\Core\Location\Entities\State;
use ValuePad\DAL\Appraiser\Types\BusinessTypesType;
use ValuePad\DAL\Appraiser\Types\CompanyTypeType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AppraiserMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->createField('firstName', 'string')
			->length(self::FIRST_NAME_LENGTH)
			->build();

		$builder->createField('lastName', 'string')
			->length(self::LAST_NAME_LENGTH)
			->build();

        $builder
            ->createField('fullName', 'string')
            ->build();

		$builder->createField('phone', 'string')
			->length(self::PHONE_LENGTH)
			->build();

		$builder->createField('cell', 'string')
			->length(self::PHONE_LENGTH)
			->build();

		$builder->createField('fax', 'string')
			->length(self::PHONE_LENGTH)
			->nullable(true)
			->build();

		// location

		$builder->createField('address1', 'string')
			->length(self::ADDRESS_LENGTH)
			->build();

		$builder->createField('address2', 'string')
			->length(self::ADDRESS_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('zip', 'string')
			->length(self::ZIP_LENGTH)
			->build();

		$builder->createField('city', 'string')
			->length(self::CITY_LENGTH)
			->build();

		$builder->createManyToOne('state', State::class)
			->addJoinColumn('state', 'code')
			->build();

		// assignment location

		$builder->createField('assignmentAddress1', 'string')
			->length(self::ADDRESS_LENGTH)
			->build();

		$builder->createField('assignmentAddress2', 'string')
			->length(self::ADDRESS_LENGTH)
			->build();

		$builder->createField('assignmentZip', 'string')
			->length(self::ZIP_LENGTH)
			->build();

		$builder->createField('assignmentCity', 'string')
			->length(self::CITY_LENGTH)
			->build();

		$builder->createManyToOne('assignmentState', State::class)
			->addJoinColumn('assignment_state', 'code')
			->build();

		//-------

		$builder->createField('companyName', 'string')
			->nullable(true)
			->build();

		$builder
			->createField('companyType', CompanyTypeType::class)
			->build();

		$builder
			->createField('otherCompanyType', 'string')
			->nullable(true)
			->build();

		$builder
			->createField('businessTypes', BusinessTypesType::class)
			->build();

		$builder->createField('taxIdentificationNumber', 'string')
			->columnName('tin')
			->length(11)
			->build();

		$builder
			->createManyToOne('w9', Document::class)
			->build();

		$builder
			->createManyToMany('languages', Language::class)
			->setJoinTable('appraisers_languages')
			->addInverseJoinColumn('language_code', 'code')
			->build();

		$builder->createManyToMany('sampleReports', Document::class)
			->setJoinTable('sample_reports')
			->build();

		$builder
			->createOneToMany('relationsWithAscAppraisers', AscAppraiser::class)
			->mappedBy('appraiser')
			->build();

		$builder
			->createManyToMany('customers', Customer::class)
			->mappedBy('appraisers')
			->build();


		$builder->createField('signature', 'string')
			->length(50)
			->build();

		$builder
			->createField('signedAt', 'datetime')
			->build();

		$builder
			->createOneToOne('eo', EoEx::class)
			->cascadeRemove()
			->build();

		$builder
			->createOneToOne('qualifications', Qualifications::class)
			->cascadeRemove()
			->build();


		$builder
			->createOneToOne('availability', Availability::class)
			->cascadeRemove()
			->build();

		$builder->createOneToMany('licenses', License::class)
			->mappedBy('appraiser')
			->build();

		$builder->createOneToMany('defaultFees', DefaultFee::class)
			->mappedBy('appraiser')
			->build();

		$builder->createField('isRegistered', 'boolean')
			->build();

		$builder->createField('showInitialDisplay', 'boolean')
			->build();


        $builder
            ->createOneToOne('ach', Ach::class)
            ->cascadeRemove()
            ->build();
	}
}
