<?php
namespace ValuePad\Api\Appraiser\V2_0\Processors;

use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Core\Support\Criteria\Constraint;

class AppraisersSearchableProcessor extends BaseSearchableProcessor
{
	/**
	 * @return array
	 */
	protected function configuration()
	{
		$data = [
			'search' => [
				'companyName' => Constraint::SIMILAR,
				'firstName' => Constraint::SIMILAR,
				'lastName' => Constraint::SIMILAR,
				'fullName' => Constraint::SIMILAR,
				'zip' => Constraint::SIMILAR,
				'qualifications.primaryLicense.number' => Constraint::SIMILAR,
			],
			'filter' => [
				'qualifications.primaryLicense.isFhaApproved' => [
					'constraint' => Constraint::EQUAL,
					'type' => 'bool'
				],
				'qualifications.primaryLicense.certifications' => [
					'constraint' => Constraint::CONTAIN,
					'type' => [['enum', Certification::class]]
				],
				'qualifications.yearsLicensed' => [
					'constraint' => Constraint::EQUAL,
					'type' => 'int'
				],
				'state' => Constraint::EQUAL,
				'licenses.coverage.county' => [
					'map' => 'licenses.coverages.county',
					'constraint' => Constraint::EQUAL,
					'type' => 'int'
				],
				'licenses.coverage.zips' => [
					'map' => 'licenses.coverages.zip',
					'constraint' => Constraint::EQUAL
				],
				'licenses.state' => Constraint::EQUAL,
				'licenses.certifications' => [
					'constraint' => Constraint::CONTAIN,
					'type' => [['enum', Certification::class]]
				],
				'jobTypes' => [
					'constraint' => Constraint::CONTAIN,
					'type' => ['int']
				]
			]
		];

		$qualifications = [
			'vaQualified', 'envCapable',
			'coopQualified', 'relocationQualified', 'usdaQualified',
			'jumboQualified', 'newConstructionQualified', 'loan203KQualified',
			'reoQualified', 'deskReviewQualified', 'fieldReviewQualified'
		];

		foreach ($qualifications as $qualification){
			$data['filter']['qualifications.'.$qualification] = [
				'constraint' => Constraint::EQUAL,
				'type' => 'bool'
			];
		}

		return $data;
	}
}
