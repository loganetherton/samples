<?php
namespace ValuePad\Tests\Integrations\Fixtures;

use ValuePad\Core\Appraiser\Enums\BusinessType;
use ValuePad\Core\Appraiser\Enums\CommercialExpertise;
use ValuePad\Core\Appraiser\Enums\CompanyType;

use DateTime;

class AppraisersFixture
{
	public static function get(array $data)
	{
		$result = [
			'companyName' => 'Test Company',
			'businessTypes' => [BusinessType::CERTIFIED_MINORITY, BusinessType::LARGE_BUSINESS],
			'companyType' => CompanyType::INDIVIDUAL_SSN,
			'otherCompanyType' => 'some custom company type',
			'taxIdentificationNumber' => '555-32-3322',
			'username' => $data['username'],
			'password' => $data['password'],
			'firstName' => 'Jack',
			'lastName' => 'Black',
			'email' => 'jack.black@test.org',
			'address1' => '123 Holloway ave',
			'city' => 'San Francisco',
			'state' => 'CA',
			'zip' => '94132',
			'languages' => ['rus', 'eng'],
			'assignmentAddress1' => '123 Holloway ave',
			'assignmentCity' => 'San Francisco',
			'assignmentState' => 'CA',
			'assignmentZip' => '94132',
			'phone' => '(222) 242-1212',
			'cell' => '(222) 242-1212',
			'w9' => $data['w9'],
			'qualifications' => [
				'primaryLicense' => [
					'number' => $data['qualifications']['primaryLicense']['number'],
					'state' => $data['qualifications']['primaryLicense']['state'],
					'isFhaApproved' => true,
					'isCommercial' => true,
					'expiresAt' => (new DateTime('+1 month'))->format('c'),
					'certifications' => ['certified-general']
				],
				'yearsLicensed' => 10,
				'certifiedAt' => [
					'month' => 2,
					'year' => 2013
				],
				'vaQualified' => true,
				'fhaQualified' => true,
				'relocationQualified' => true,
				'usdaQualified' => false,
				'coopQualified' => true,
				'jumboQualified' => false,
				'newConstructionQualified' => true,
				'newConstructionExperienceInYears' => 10,
				'numberOfNewConstructionCompleted' => 2033,
				'isNewConstructionCourseCompleted' => true,
				'isFamiliarWithFullScopeInNewConstruction' => false,
				'loan203KQualified' => false,
				'manufacturedHomeQualified' => true,
				'reoQualified' => true,
				'deskReviewQualified' => true,
				'fieldReviewQualified' => true,
				'envCapable' => true,
				'commercialQualified' => true,
				'commercialExpertise' => [CommercialExpertise::LAND, CommercialExpertise::OFFICE, CommercialExpertise::RETAIL],
				'otherCommercialExpertise' => 'some stuff'
			],
			'eo' => [
				'document' => $data['eo']['document'],
				'claimAmount' => 100.02,
				'aggregateAmount' => 1000.99,
				'expiresAt' => (new DateTime('+1 month'))->format('c'),
				'carrier' => 'test',
				'deductible' => 20.11,
				'question1' => false,
				'question1Explanation' => 'Explanation #1',
				'question2' => true,
				'question2Explanation' => 'Explanation #2',
				'question3' => false,
				'question3Explanation' => 'Explanation #3',
				'question4' => true,
				'question4Explanation' => 'Explanation #4',
				'question5' => true,
				'question5Explanation' => 'Explanation #5',
				'question6' => false,
				'question6Explanation' => 'Explanation #6',
				'question7' => true,
				'question7Explanation' => 'Explanation #7',
			],
			'signature' => 'John Black',
			'signedAt' => (new DateTime())->format(DateTime::ATOM)
		];


		return $result;
	}
}
