<?php
namespace ValuePad\Api\Appraiser\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Shared\Support\AvailabilityConfigurationTrait;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\Validation\Rules\MonthYearPair;
use ValuePad\Core\Appraiser\Enums\BusinessType;
use ValuePad\Core\Appraiser\Enums\CommercialExpertise;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Persistables\AppraiserPersistable;
use ValuePad\Api\Appraiser\V2_0\Support\LicenseConfigurationTrait;
use ValuePad\Core\User\Enums\Status;

class AppraisersProcessor extends BaseProcessor
{
    use LicenseConfigurationTrait;
	use AvailabilityConfigurationTrait;

    /**
     * @return array
     */
    protected function configuration()
    {
        $config = array_merge($this->getLicenseConfiguration(['namespace' => 'qualifications.primaryLicense']),
			$this->getAvailabilityConfiguration(['namespace' => 'availability']),
			[
				'firstName' => 'string',
				'lastName' => 'string',
				'email' => 'string',
				'username' => 'string',
				'password' => 'string',

				'showInitialDisplay' => 'bool',
				'companyName' => 'string',
				'businessTypes' => [new Enum(BusinessType::class)],
				'companyType' => new Enum(CompanyType::class),
				'otherCompanyType' => 'string',
				'taxIdentificationNumber' => 'string',
				'w9' => 'document',

				'languages' => 'string[]',

				'address1' => 'string',
				'address2' => 'string',
				'city' => 'string',
				'state' => 'string',
				'zip' => 'string',

				'assignmentAddress1' => 'string',
				'assignmentAddress2' => 'string',
				'assignmentState' => 'string',
				'assignmentCity' => 'string',
				'assignmentZip' => 'string',

				'phone' => 'string',
				'cell' => 'string',
				'fax' => 'string',

				'qualifications.yearsLicensed' => 'int',
				'qualifications.certifiedAt' => new MonthYearPair(),
				'qualifications.vaQualified' => 'bool',
				'qualifications.fhaQualified' => 'bool',
				'qualifications.relocationQualified' => 'bool',
				'qualifications.usdaQualified' => 'bool',
				'qualifications.coopQualified' => 'bool',
				'qualifications.jumboQualified' => 'bool',

				'qualifications.newConstructionQualified' => 'bool',
				'qualifications.newConstructionExperienceInYears' => 'int',
				'qualifications.numberOfNewConstructionCompleted' => 'int',
				'qualifications.isNewConstructionCourseCompleted' => 'bool',
				'qualifications.isFamiliarWithFullScopeInNewConstruction' => 'bool',

				'qualifications.loan203KQualified' => 'bool',
				'qualifications.manufacturedHomeQualified' => 'bool',
				'qualifications.reoQualified' => 'bool',
				'qualifications.deskReviewQualified' => 'bool',
				'qualifications.fieldReviewQualified' => 'bool',
				'qualifications.envCapable' => 'bool',
				'qualifications.commercialQualified' => 'bool',
				'qualifications.commercialExpertise' => [new Enum(CommercialExpertise::class)],
				'qualifications.otherCommercialExpertise' => 'string',
				'qualifications.resume' => 'document',

				'eo.document' => 'document',
				'eo.claimAmount' => 'float',
				'eo.aggregateAmount' => 'float',
				'eo.expiresAt' => 'datetime',
				'eo.carrier' => 'string',
				'eo.deductible' => 'float',
				'eo.question1' => 'bool',
				'eo.question1Explanation' => 'string',
				'eo.question1Document' => 'document',
				'eo.question2' => 'bool',
				'eo.question2Explanation' => 'string',
				'eo.question3' => 'bool',
				'eo.question3Explanation' => 'string',
				'eo.question4' => 'bool',
				'eo.question4Explanation' => 'string',
				'eo.question5' => 'bool',
				'eo.question5Explanation' => 'string',
				'eo.question6' => 'bool',
				'eo.question6Explanation' => 'string',
				'eo.question7' => 'bool',
				'eo.question7Explanation' => 'string',

				'sampleReports' => 'document[]',
				'signature' => 'string',
				'signedAt' => 'datetime'
        	]
		);

		if ($this->isAdmin()){
			$config['status'] = new Enum(Status::class);
		}

		return $config;
    }

    /**
     * @return AppraiserPersistable
     */
    public function createPersistable()
    {
        return $this->populate(
			new AppraiserPersistable(),
			$this->getPopulatorConfig(['namespace' => 'qualifications.primaryLicense'])
		);
    }

	/**
	 * @return bool
	 */
	public function isSoftValidationMode()
	{
		return strtolower($this->getRequest()->header('Soft-Validation-Mode')) === strtolower('true');
	}
}
