<?php
namespace ValuePad\DAL\Appraiser\Metadata;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ValuePad\Core\Appraiser\Entities\License;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\DAL\Appraiser\Types\CommercialExpertiseType;
use ValuePad\DAL\Shared\Types\MonthYearPairType;
use ValuePad\DAL\Support\Metadata\AbstractMetadataProvider;

class QualificationsMetadata extends AbstractMetadataProvider
{
	/**
	 * @param ClassMetadataBuilder $builder
	 * @return void
	 */
	public function define(ClassMetadataBuilder $builder)
	{
		$builder->setTable('qualifications');
		$this->defineId($builder);

		$builder
			->createManyToOne('resume', Document::class)
			->build();


		$builder
			->createOneToOne('primaryLicense', License::class)
			->build();

		$flags = [
			'vaQualified',
			'fhaQualified',
			'relocationQualified',
			'usdaQualified',
			'coopQualified',
			'jumboQualified',
			'newConstructionQualified',
			'loan203KQualified',
			'manufacturedHomeQualified',
			'reoQualified',
			'deskReviewQualified',
			'fieldReviewQualified',
			'envCapable',
			'commercialQualified',
		];

		foreach ($flags as $flag){
			$builder
				->createField($flag, 'boolean')
				->nullable(true)
				->build();
		}

		$builder
			->createField('newConstructionExperienceInYears', 'integer')
			->nullable(true)
			->build();

		$builder
			->createField('numberOfNewConstructionCompleted', 'integer')
			->nullable(true)
			->build();


		$builder
			->createField('isNewConstructionCourseCompleted', 'boolean')
			->nullable(true)
			->build();

		$builder
			->createField('isFamiliarWithFullScopeInNewConstruction', 'boolean')
			->nullable(true)
			->build();


		$builder
			->createField('commercialExpertise', CommercialExpertiseType::class)
			->nullable(true)
			->build();

		$builder
			->createField('otherCommercialExpertise', 'string')
			->nullable(true)
			->build();

		$builder
			->createField('yearsLicensed', 'integer')
			->build();

		$builder
			->createField('certifiedAt', MonthYearPairType::class)
			->nullable(true)
			->build();
	}
}
