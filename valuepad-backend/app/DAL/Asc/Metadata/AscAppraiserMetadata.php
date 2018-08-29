<?php
namespace ValuePad\DAL\Asc\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Location\Entities\State;
use ValuePad\DAL\Asc\Types\CertificationsType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class AscAppraiserMetadata extends AbstractMetadataProvider
{
    /**
     * @param ClassMetadataBuilder $builder
     * @return void
     */
    public function define(ClassMetadataBuilder $builder)
    {
        $builder->setTable('asc_gov');

		$this->defineId($builder);

		$builder->addUniqueConstraint(['license_state', 'license_number'], 'appraiser_identifier');

		$builder->createField('firstName', 'string')
			->length(self::FIRST_NAME_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('lastName', 'string')
			->length(self::LAST_NAME_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('phone', 'string')
			->length(self::PHONE_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('companyName', 'string')
			->nullable(true)
			->build();

		$builder
			->createField('licenseNumber', 'string')
			->length(self::LICENSE_NUMBER_LENGTH)
			->build();

		$builder
			->createManyToOne('licenseState', State::class)
			->addJoinColumn('license_state', 'code')
			->build();

		$builder
			->createField('licenseExpiresAt', 'datetime')
			->build();

		$builder
			->createField('certifications', CertificationsType::class)
			->build();

		$builder->createManyToOne('appraiser', Appraiser::class)
			->inversedBy('relationsWithAscAppraisers')
			->build();

		$builder->createField('address', 'string')
			->length(self::ADDRESS_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('zip', 'string')
			->length(self::ZIP_LENGTH)
			->nullable(true)
			->build();

		$builder->createField('city', 'string')
			->length(self::CITY_LENGTH)
			->nullable(true)
			->build();

		$builder->createManyToOne('state', State::class)
			->addJoinColumn('state', 'code')
			->build();
    }
}
