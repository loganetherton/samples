<?php
namespace ValuePad\Tests\Integrations\Fixtures;
use DateTime;
use ValuePad\Core\Appraisal\Enums\ConcessionUnit;
use ValuePad\Tests\Integrations\Support\Runtime\Helper;
use ValuePad\Core\Appraisal\Enums\Property\ValueQualifier;
use ValuePad\Core\Appraisal\Enums\Property\ValueType;
class OrdersFixture
{
	const DEFAULT = 1;
	const DIFFERENT = 2;

	public static function getAsBidRequest(Helper $helper, array $data)
	{
		$data = self::get($helper, $data);
		unset($data['dueDate'], $data['fee']);

		$data['isBidRequest'] = true;

		return $data;
	}

	/**
	 * @param Helper $helper
	 * @param array $data
	 * @param int $scenario
	 * @return array
	 */
	public static function get(Helper $helper, array $data, $scenario = self::DEFAULT)
	{
		if ($scenario === self::DIFFERENT){
			return self::getDifferent($helper, $data);
		}

		return self::getDefault($helper, $data);
	}

	private static function getDefault(Helper $helper, array $data)
	{
		return [
			'fileNumber' => 'AAABBB000',
			'client' => $data['client'],
			'clientDisplayedOnReport' => $data['clientDisplayedOnReport'],
			'referenceNumber' => 'CCCDDDD111',
			'amcLicenseNumber' => 'ABCDEFG123456',
			'amcLicenseExpiresAt' => (new DateTime('+ 2 months'))->format(DateTime::ATOM),
			'jobType' => 10,
			'fee' => 1000,
			'purchasePrice' => 0.29,
			'fhaNumber' => '123456789abc',
			'loanNumber' => 'abcd1234',
			'loanType' => 'some type',
			'loanAmount' => 32.22,
			'contractDate' => (new DateTime('2011-01-12 20:01:11'))->format(DateTime::ATOM),
			'salesPrice' => 201.22,
			'concession' => 933.2,
			'concessionUnit' => ConcessionUnit::AMOUNT,
			'approachesToBeIncluded' => ['income', 'cost'],
			'dueDate' => (new DateTime('+ 3 weeks'))->format(DateTime::ATOM),
			'orderedAt' => (new DateTime('-3 days'))->format(DateTime::ATOM),
			'property' => [
				'type' => 'property type',
				'viewType' => 'property view type',
				'approxBuildingSize' => 100.20,
				'approxLandSize' => 2000.10,
				'buildingAge' => 10,
				'numberOfStories' => 19,
				'numberOfUnits' => 101,
				'grossRentalIncome' => 10.41,
				'incomeSalesCost' => 199.22,
				'valueTypes' => [ValueType::MARKET],
				'valueQualifiers' => [ValueQualifier::AS_COMPLETE, ValueQualifier::AS_PROPOSED],
				'ownerInterests' => ['leased-fee'],
				'address1' => '231 Www Str.',
				'address2' => '232 Www Str.',
				'state' => 'TX',
				'city' => 'San Texas',
				'zip' => '76333',
				'county' => $helper->county('ROCKWALL', 'TX'),
				'occupancy' => 'new-construction',
				'bestPersonToContact' => 'owner',
				'contacts' => [
					[
						'type' => 'borrower',
						'name' => 'Test & Co',
						'firstName' => 'George',
						'lastName' => 'Smith',
						'middleName' => 'Mike',
						'homePhone' => '(333) 444-4444',
						'cellPhone' => '(444) 555-5555',
						'workPhone' => '(555) 666-6666',
						'email' => 'george.smith@test.org'
					],
					[
						'type' => 'owner',
						'firstName' => 'James',
						'lastName' => 'Brown',
						'middleName' => 'Antony',
						'homePhone' => '(666) 777-7777',
						'cellPhone' => '(888) 999-9999',
						'workPhone' => '(999) 000-0000',
						'email' => 'james.brown@test.org',
					]
				],
				'legal' => 'some legal text',
				'additionalComments' => 'whatever'
			],
			'instructionDocuments' => [
				[
					'url' => 'http://blog.igorvorobiov.com/1.png',
					'name' => '1.png',
					'format' => 'png',
					'size' => 100033
				],
				[
					'url' => 'http://blog.igorvorobiov.com/2.jpg',
					'name' => '2.png',
					'format' => 'jpeg',
					'size' => 77777
				]
			],
			'instruction' => 'some point to tell what to do',
			'additionalDocuments' => [
				[
					'url' => 'http://blog.igorvorobiov.com/3.png',
					'name' => '3.png',
					'format' => 'jpeg',
					'size' => 666666
				],
				[
					'url' => 'http://blog.igorvorobiov.com/4.jpg',
					'name' => '4.png',
					'format' => 'png',
					'size' => 222225222
				]
			]
		];
	}

	private static function getDifferent(Helper $helper, array $data)
	{
		return [
			'fileNumber' => 'ZZZYYY999',
			'referenceNumber' => 'NNNMMM222',
			'client' => $data['client'],
			'clientDisplayedOnReport' => $data['clientDisplayedOnReport'],
			'amcLicenseNumber' => 'XXXXXXXXXX00',
			'amcLicenseExpiresAt' => (new DateTime('+ 3 months'))->format(DateTime::ATOM),
			'jobType' => 9,
			'fee' => 333,
			'purchasePrice' => 23.98,
			'fhaNumber' => '222222222ccc',
			'loanNumber' => 'ffff0000',
			'loanType' => 'some type 2',
			'loanAmount' => 10.01,
			'contractDate' => (new DateTime('2012-05-12 20:01:11'))->format(DateTime::ATOM),
			'salesPrice' => 90.12,
			'concession' => 22,
			'concessionUnit' => ConcessionUnit::PERCENTAGE,
			'approachesToBeIncluded' => ['sales'],
            'isRush' => true,
			'dueDate' => (new DateTime('+ 2 weeks'))->format(DateTime::ATOM),
			'orderedAt' => (new DateTime('-5 days'))->format(DateTime::ATOM),
			'property' => [
				'type' => 'property type 2',
				'viewType' => 'property view type 2',
				'approxBuildingSize' => 99.2,
				'approxLandSize' => 63.10,
				'buildingAge' => 89,
				'numberOfStories' => 442,
				'numberOfUnits' => 12,
				'grossRentalIncome' => 88.21,
				'incomeSalesCost' => 78.98,
				'valueTypes' => [ValueType::INSURABLE],
				'valueQualifiers' => [ValueQualifier::GOING_CONCERN],
				'ownerInterests' => ['duplex'],
				'address1' => '777 ggg Str.',
				'address2' => '888 ggg Str.',
				'state' => 'NV',
				'city' => 'Las Vegas',
				'zip' => '99744',
				'county' => $helper->county('STOREY', 'NV'),
				'occupancy' => 'tenant',
				'bestPersonToContact' => 'borrower',
				'contacts' => [
					[
						'type' => 'borrower',
						'name' => 'Test 2 & Co',
						'firstName' => 'Tony',
						'lastName' => 'Lee',
						'middleName' => 'John',
						'homePhone' => '(123) 456-7890',
						'cellPhone' => '(555) 753-4444',
						'workPhone' => '(765) 211-0866',
						'email' => 'tony.lee@test.org'
					]
				],
				'legal' => 'some legal text 2',
				'additionalComments' => 'whatever 2'
			],
			'instructionDocuments' => [
				[
					'url' => 'http://blog.igorvorobiov.com/cool-document.jpg',
					'name' => 'cool-document.png',
					'format' => 'jpeg',
					'size' => 8995
				]
			],
			'instruction' => 'some point to tell what to do 2',
			'additionalDocuments' => [
				[
					'url' => 'http://blog.igorvorobiov.com/book.png',
					'name' => 'book.png',
					'format' => 'png',
					'size' => 64322
				]
			]
		];
	}
}
