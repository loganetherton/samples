<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Entities\Document;
use ValuePad\Core\Appraisal\Entities\AdditionalDocument;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Tests\Integrations\Support\Freezer;
use ValuePad\Tests\Integrations\Support\Runtime\Helper;
use ValuePad\Core\Appraisal\Enums\ConcessionUnit;
use ValuePad\Core\Appraisal\Enums\Property\ValueQualifier;
use ValuePad\Core\Appraisal\Enums\Property\ValueType;
use ValuePad\Core\Appraisal\Enums\Request as AppraiserRequest;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Enums\AssetType;

$amcLicenseExpiresAt = (new DateTime('+ 2 months'))->format(DateTime::ATOM);
$dueDate = (new DateTime('+ 3 weeks'))->format(DateTime::ATOM);
$orderedAt = (new DateTime('-3 days'))->format(DateTime::ATOM);
$ecd = (new DateTime('+1 month'))->format(DateTime::ATOM);
$assignedAt = (new DateTime('-2 years'))->format(DateTime::ATOM);

$freezer = Freezer::getInstance('V2_0/Customer/Orders.php');

$freezer->register('requestBody', function(Helper $helper) use ($amcLicenseExpiresAt, $dueDate, $orderedAt){
	$data = array_merge(OrdersFixture::get($helper, [
		'client' => 1,
		'clientDisplayedOnReport' => 2
	]), [
	    'intendedUse' => 'Loan',
		'amcLicenseExpiresAt' => $amcLicenseExpiresAt,
		'dueDate' => $dueDate,
		'orderedAt' => $orderedAt,
		'additionalJobTypes' => [11, 12, 13],
		'techFee' => 999.99,
        'fdic' => [
            'fin' => '99999',
            'taskOrder' => '7711',
            'line' => 3,
            'contractor' => 'contractor_1414',
            'assetNumber' => '123456789012',
            'assetType' => AssetType::ORE
        ],
	]);

    $data['property']['characteristics'] = ['aaa', 'bbb', 'ccc'];
    $data['property']['contacts'][0]['intentProceedDate'] = (new DateTime())->format(DateTime::ATOM);
    $data['property']['contacts'][1]['intentProceedDate'] = (new DateTime())->format(DateTime::ATOM);

    return $data;
});

$freezer->register('requestUpdatedBody', function(Helper $helper){
	$data =  OrdersFixture::get($helper, [
		'client' => 3,
		'clientDisplayedOnReport' => 4
	], OrdersFixture::DIFFERENT);

	$data['additionalJobTypes'] = [13, 20];
	$data['techFee'] = 219.91;

    $data['fdic'] = [
        'fin' => '11111',
        'taskOrder' => '2222',
        'line' => 2,
        'contractor' => 'contractor_8888',
        'assetNumber' => '444444444444',
        'assetType' => AssetType::SETTLEMENT
    ];

    $data['intendedUse'] = 'OLE';
    $data['property']['characteristics'] = ['xxx', 'yyy', 'zzz'];

	return $data;
});

$freezer->register('responseBody', function(Helper $helper) use ($amcLicenseExpiresAt, $dueDate, $orderedAt){
	return [
		'id' => new Dynamic(Dynamic::INT),
        'intendedUse' => 'Loan',
		'fileNumber' => 'AAABBB000',
		'referenceNumber' => 'CCCDDDD111',
		'client' => [
			'id' => new Dynamic(Dynamic::INT),
			'name' => 'Good Client',
			'address1' => '1st Street',
			'address2' => '2nd Street',
			'city' => 'San Pedro',
			'state' => [
				'code' => 'FL',
				'name' => 'Florida'
			],
			'zip' => '59322',
		],
		'clientName' => 'Good Client',
		'clientAddress1' => '1st Street',
		'clientAddress2' => '2nd Street',
		'clientCity' => 'San Pedro',
		'clientState' => [
			'code' => 'FL',
			'name' => 'Florida'
		],
		'clientZip' => '59322',
		'clientDisplayedOnReport' => [
			'id' => new Dynamic(Dynamic::INT),
			'name' => 'Best Client',
			'address1' => '3rd Street',
			'address2' => '4th Street',
			'city' => 'Los Animals',
			'state' => [
				'code' => 'CA',
				'name' => 'California'
			],
			'zip' => '95222',
		],
		'clientDisplayedOnReportName' => 'Best Client',
		'clientDisplayedOnReportAddress1' => '3rd Street',
		'clientDisplayedOnReportAddress2' => '4th Street',
		'clientDisplayedOnReportCity' => 'Los Animals',
		'clientDisplayedOnReportState' => [
			'code' => 'CA',
			'name' => 'California'
		],
		'clientDisplayedOnReportZip' => '95222',
		'amcLicenseNumber' => 'ABCDEFG123456',
		'amcLicenseExpiresAt' => $amcLicenseExpiresAt,
		'jobType' => [
			'id' => 10,
			'isCommercial' => false,
			'isPayable' => true,
			'title' => new Dynamic(Dynamic::STRING),
			'local' => new Dynamic(function($v){
				return is_array($v);
			})
		],
		'additionalJobTypes' => new Dynamic(function($value){
			return array_equal([11, 12, 13], array_map(function($row){
				return $row['id'];
			}, $value));
		}),
		'fee' => 1000,
		'techFee' => 999.99,
		'purchasePrice' => 0.29,
		'fhaNumber' => '123456789abc',
		'loanNumber' => 'abcd1234',
		'loanType' => 'some type',
		'loanAmount' => 32.22,
		'contractDocument' => null,
		'contractDate' => (new DateTime('2011-01-12 20:01:11'))->format(DateTime::ATOM),
		'salesPrice' => 201.22,
		'concession' => 933.2,
		'concessionUnit' => ConcessionUnit::AMOUNT,
		'processStatus' => 'new',
		'approachesToBeIncluded' => ['income', 'cost'],
		'dueDate' => $dueDate,
		'orderedAt' => $orderedAt,
		'inspectionCompletedAt' => null,
		'estimatedCompletionDate' => null,
		'completedAt' => null,
		'assignedAt' => new Dynamic(Dynamic::DATETIME),
        'fdic' => [
            'fin' => '99999',
            'taskOrder' => '7711',
            'line' => 3,
            'contractor' => 'contractor_1414',
            'assetNumber' => '123456789012',
            'assetType' => AssetType::ORE
        ],
		'property' => [
			'type' => 'property type',
			'viewType' => 'property view type',
            'characteristics' => ['aaa', 'bbb', 'ccc'],
			'approxBuildingSize' => 100.20,
			'approxLandSize' => 2000.10,
			'buildingAge' => 10,
			'numberOfStories' => 19,
			'numberOfUnits' => 101,
			'grossRentalIncome' => 10.41,
			'incomeSalesCost' => 199.22,
			'valueTypes' => [ValueType::MARKET],
			'valueQualifiers' => [ValueQualifier::AS_COMPLETE, ValueQualifier::AS_PROPOSED],
			'ownerInterest' => 'leased-fee',
			'ownerInterests' => ['leased-fee'],
			'address1' => '231 Www Str.',
			'address2' => '232 Www Str.',
			'state' => [
				'code' => 'TX',
				'name' => 'Texas'
			],
			'city' => 'San Texas',
			'zip' => '76333',
			'county' => [
				'id' => $helper->county('ROCKWALL', 'TX'),
				'title' => new Dynamic(Dynamic::STRING)
			],
			'latitude' => null,
			'longitude' => null,
			'displayAddress' => '231 Www Str., San Texas, TX 76333',
			'occupancy' => 'new-construction',
			'bestPersonToContact' => 'owner',
			'contacts' => [
				[
					'type' => 'borrower',
					'name' => 'Test & Co',
					'firstName' => 'George',
					'lastName' => 'Smith',
					'middleName' => 'Mike',
                    'displayName' => 'Test & Co',
					'homePhone' => '(333) 444-4444',
					'cellPhone' => '(444) 555-5555',
					'workPhone' => '(555) 666-6666',
                    'intentProceedDate' => new Dynamic(Dynamic::DATETIME),
					'email' => 'george.smith@test.org'
				],
				[
					'type' => 'owner',
					'name' => null,
					'firstName' => 'James',
					'lastName' => 'Brown',
					'middleName' => 'Antony',
                    'displayName' => 'James Antony Brown',
					'homePhone' => '(666) 777-7777',
					'cellPhone' => '(888) 999-9999',
					'workPhone' => '(999) 000-0000',
                    'intentProceedDate' => new Dynamic(Dynamic::DATETIME),
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
		],
		'isRush' => false,
		'isPaid' => false,
		'paidAt' => null,
		'customer' => new Dynamic(function($value){
			return is_array($value);
		})
	];
});


$includes = [
	'referenceNumber', 'intendedUse',
	'client', 'clientDisplayedOnReport',
	'clientName', 'clientAddress1',
	'clientAddress2', 'clientCity',
	'clientState', 'clientZip',
	'clientDisplayedOnReportName', 'clientDisplayedOnReportAddress1',
	'clientDisplayedOnReportAddress2', 'clientDisplayedOnReportCity',
	'clientDisplayedOnReportState', 'clientDisplayedOnReportZip',
	'amcLicenseNumber', 'amcLicenseExpiresAt',
	'jobType', 'fee',
	'techFee', 'purchasePrice',
	'fhaNumber', 'loanNumber',
	'loanType', 'loanAmount', 'approachesToBeIncluded',
	'contractDocument', 'contractDate', 'salesPrice', 'concession', 'concessionUnit',
	'dueDate', 'orderedAt',
	'completedAt', 'inspectionCompletedAt',
	'estimatedCompletionDate', 'processStatus',
	'assignedAt', 'property',
	'instructionDocuments', 'instruction',
	'additionalDocuments',
    'isRush',
	'isPaid',
	'paidAt',
	'additionalJobTypes',
	'customer',
    'fdic'
];

return [
	'createContractDocument1:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'createContractDocument2:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'validate' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => [
					'fileNumber' => '  ',
                    'intendedUse' => ' ',
					'referenceNumber' => ' ',
					'client' => 2000,
					'clientDisplayedOnReport' => 2001,
					'amcLicenseNumber' => ' ',
					'amcLicenseExpiresAt' => (new DateTime())->format(DateTime::ATOM),
					'jobType' => 9999,
					'additionalJobTypes' => [11, 20, 20, 11],
					'fee' => -19.01,
					'techFee' => -10,
					'purchasePrice' => -199,
					'fhaNumber' => '!@#%#$^#$^',
					'loanNumber' => ' ',
					'loanType' => '  ',
					'loanAmount' => -1,
					'concession' => -10,
					'salesPrice' => -102,
					'orderedAt' => (new DateTime('+1 week'))->format(DateTime::ATOM),
                    'fdic' => [
                        'fin' => '$1234',
                        'taskOrder' => '&243',
                        'line' => 5,
                        'contractor' => '',
                        'assetNumber' => '2#4',
                    ],
					'property' => [
						'type' => ' ',
						'approxBuildingSize' => 0,
						'approxLandSize' => 0,
						'buildingAge' => 0,
						'numberOfStories' => 0,
						'numberOfUnits' => 0,
						'grossRentalIncome' => -10,
						'incomeSalesCost' => -1003,
						'address1' => ' ',
						'address2' => ' ',
						'state' => 'HHF',
						'city' => ' ',
						'zip' => '@#$@#$',
						'county' => 99999,
						'contacts' => [
							[
								'type' => 'borrower'
							],
							[
								'type' => 'borrower'
							]
						],
						'legal' => ' ',
					],
					'instructionDocuments' => [
						[
							'url' => ' ',
							'name' => '  ',
							'size' => 0
						],
						[
							'size' => -139
						]
					],
					'instruction' => '   ',
					'additionalDocuments' => [
						[
							'url' => '  ',
							'name' => '  ',
						]
					],
					'contractDocument' => [
						'type' => null
					]
				]
			],
			'response' => [
				'errors' => [
					'fileNumber' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
                    'intendedUse' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
					'referenceNumber' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'client' => [
						'identifier' => 'not-belong',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'clientDisplayedOnReport' => [
						'identifier' => 'not-belong',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'amcLicenseNumber' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'amcLicenseExpiresAt' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'jobType' => [
						'identifier' => 'not-belong',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'additionalJobTypes' => [
						'identifier' => 'unique',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'fee' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'techFee' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'purchasePrice' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'fhaNumber' => [
						'identifier' => 'format',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'loanNumber' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'loanType' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'loanAmount' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'concession' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'salesPrice' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'orderedAt' => [
						'identifier' => 'less',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
                    'fdic.fin' => [
                        'identifier' => 'alphanumeric',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'fdic.taskOrder' => [
                        'identifier' => 'alphanumeric',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'fdic.line' => [
                        'identifier' => 'less',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'fdic.contractor' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'fdic.assetNumber' => [
                        'identifier' => 'alphanumeric',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
					'property.type' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.approxBuildingSize' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.approxLandSize' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.buildingAge' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.numberOfStories' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.numberOfUnits' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.grossRentalIncome' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.incomeSalesCost' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.address1' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.address2' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.state' => [
						'identifier' => 'length',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.city' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.zip' => [
						'identifier' => 'format',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.contacts' => [
						'identifier' => 'unique',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.legal' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.bestPersonToContact' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'property.occupancy' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'instructionDocuments' => [
						'identifier' => 'collection',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => [
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'url' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'name' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'size' => [
										'identifier' => 'greater',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'format' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							],
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'url' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'name' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'size' => [
										'identifier' => 'greater',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'format' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							]
						]
					],
					'instruction' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'additionalDocuments' => [
						'identifier' => 'collection',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => [
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'url' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'name' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'size' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'format' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							]
						]
					],
					'contractDocument.label' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'contractDocument.document' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'create' => function(Runtime $runtime) use ($includes, $freezer){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$request = $freezer->get('requestBody', $runtime->getHelper());

		$request['contractDocument'] = [
			'document' => [
				'id' => $runtime->getCapture()->get('createContractDocument1.id'),
				'token' => $runtime->getCapture()->get('createContractDocument1.token')
			],
			'label' => 'Contract Document #1'
		];

		$response = $freezer->get('responseBody', $runtime->getHelper());
		$response['contractDocument'] = [
			'id' => new Dynamic(Dynamic::INT),
			'type' => null,
			'label' => 'Contract Document #1',
			'document' => $runtime->getCapture()->get('createContractDocument1'),
			'createdAt' => new Dynamic(Dynamic::DATETIME)
		];

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'includes' => $includes,
				'auth' => 'customer',
				'body' => $request
			],
			'response' => [
				'body' => $response
			],
			'live' => function(Runtime $runtime) {
				$capture = $runtime->getCapture();
				return [
					'body' => [
						[
                            'channels' => [
                                'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                                'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                            ],
							'event' => 'order:create-log',
							'data' => new Dynamic(function($data){
								return $data['action'] === Action::CREATE_ORDER;
							})
						],
						[
                            'channels' => [
                                'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                                'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                            ],
							'event' => 'order:create',
							'data' => [
								'id' => $capture->get('create.id'),
								'fileNumber' => $capture->get('create.fileNumber'),
								'processStatus' => $capture->get('create.processStatus')
							]
						],
					]
				];
			},

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.displayName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'New - Order on '.$capture->get('create.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			},

			'mobile' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return [
					'body' => [
						[
							'users' => [$session->get('user.id')],
							'notification' => [
								'category' => 'order',
								'name' => 'create'
							],
							'message' => new Dynamic(function($value) use ($capture){
								$property = $capture->get('create.property');

								return str_contains($value, $property['address1'].', '.$property['city'].', '.$property['state']['code'].' '.$property['zip']);
							}),
							'extra' => [
								'order' => $capture->get('create.id'),
								'fileNumber' => $capture->get('create.fileNumber'),
								'processStatus' => ProcessStatus::FRESH
							]
						]
					]
				];
			}
		];
	},

	'get' => function(Runtime $runtime) use ($includes){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'includes' => $includes,
				'auth' => 'customer'
			],
			'response' => [
				'body' => $capture->get('create')
			]
		];
	},

	'getLogsForCreate' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		$capture = $runtime->getCapture();

		$order = $capture->get('create');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$order['id'].'/logs',
			],
			'response' => [
				'body' => [
					[
						'id' => new Dynamic(Dynamic::INT),
						'action' => Action::CREATE_ORDER,
						'actionLabel' => 'New Order',
						'message' => sprintf(
							'You have received a new order on %s, %s, %s %s from %s.',
							$order['property']['address1'],
							$order['property']['city'],
							$order['property']['state']['code'],
							$order['property']['zip'],
							$order['customer']['name']

						),
						'user' => new Dynamic(function($data) use ($customerSession){
							return $data['id'] == $customerSession->get('user.id');
						}),
						'order' => new Dynamic(function($data) use ($capture){
							return $data['id'] == $capture->get('create.id');
						}),
						'extra' => [
							'user' => $customerSession->get('user.name'),
							'customer' => $order['customer']['name'],
							'address1' => $capture->get('create.property.address1'),
							'address2' => $capture->get('create.property.address2'),
							'city' => $capture->get('create.property.city'),
							'zip' => $capture->get('create.property.zip'),
							'state' => $capture->get('create.property.state'),
						],
						'createdAt' => new Dynamic(Dynamic::DATETIME)
					]
				]
			]
		];
	},

	'createWithAssignedAt' => function(Runtime $runtime) use ($assignedAt){

		$customer = $runtime->getSession('customer');
		$appraiser = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['assignedAt'] = $assignedAt;

		return [
			'request' => [
				'url' => 'POST /customers/'.$customer->get('user.id').'/appraisers/'.$appraiser->get('user.id').'/orders',
				'includes' => ['assignedAt'],
				'auth' => 'customer',
				'body' => $data
			],
			'response' => [
				'body' => [
					'assignedAt' => $assignedAt
				],
				'filter' => new ItemFieldsFilter(['assignedAt'], true)
			]
		];
	},

	'getWithAssignedAt' => function(Runtime $runtime) use ($includes, $assignedAt){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createWithAssignedAt.id'),
				'includes' => ['assignedAt'],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'assignedAt' => $assignedAt
				],
				'filter' => new ItemFieldsFilter(['assignedAt'], true)
			]
		];
	},

	'createWithMinimum' => function(Runtime $runtime) use ($includes, $freezer){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$requestBody = $freezer->get('requestBody', $runtime->getHelper());
		$responseBody = $freezer->get('responseBody', $runtime->getHelper());

		unset(
			$requestBody['additionalJobTypes'],
            $requestBody['intendedUse'],
			$requestBody['referenceNumber'],
			$requestBody['techFee'],
			$requestBody['dueDate'],
			$requestBody['purchasePrice'],
			$requestBody['fhaNumber'],
			$requestBody['loanNumber'],
			$requestBody['contractDate'],
			$requestBody['concession'],
			$requestBody['concessionUnit'],
			$requestBody['salesPrice'],
            $requestBody['fdic'],
            $requestBody['property']['characteristics'],
			$requestBody['property']['approxBuildingSize'],
			$requestBody['property']['approxLandSize'],
			$requestBody['property']['buildingAge'],
			$requestBody['property']['numberOfStories'],
			$requestBody['property']['numberOfUnits'],
			$requestBody['property']['grossRentalIncome'],
			$requestBody['property']['incomeSalesCost'],
			$requestBody['property']['address2'],
			$requestBody['property']['additionalComments'],
			$requestBody['property']['valueTypes'],
			$requestBody['property']['ownerInterests'],
			$requestBody['property']['valueQualifiers'],
			$requestBody['property']['contacts'],
			$requestBody['property']['legal'],
			$requestBody['loanType'],
			$requestBody['loanAmount'],
			$requestBody['amcLicenseNumber'],
			$requestBody['amcLicenseExpiresAt'],
			$requestBody['additionalDocuments'],
			$requestBody['instructionDocuments'],
			$requestBody['instruction'],
			$requestBody['approachesToBeIncluded']
		);

		$requestBody['property']['bestPersonToContact'] = 'borrower';

		$responseBody['additionalJobTypes'] = [];
        $responseBody['intendedUse'] = null;
		$responseBody['referenceNumber'] = null;
		$responseBody['loanNumber'] = null;
		$responseBody['dueDate'] = null;
		$responseBody['techFee'] = null;
		$responseBody['purchasePrice'] = null;
		$responseBody['fhaNumber'] = null;
		$responseBody['loanNumber'] = null;
		$responseBody['contractDocument'] = null;
		$responseBody['contractDate'] = null;
		$responseBody['salesPrice'] = null;
		$responseBody['concession'] = null;
		$responseBody['concessionUnit'] = null;
        $responseBody['fdic'] = null;
		$responseBody['property']['characteristics'] = [];
		$responseBody['property']['approxBuildingSize'] = null;
		$responseBody['property']['approxLandSize'] = null;
		$responseBody['property']['buildingAge'] = null;
		$responseBody['property']['numberOfStories'] = null;
		$responseBody['property']['numberOfUnits'] = null;
		$responseBody['property']['grossRentalIncome'] = null;
		$responseBody['property']['incomeSalesCost'] = null;
		$responseBody['property']['address2'] = null;
		$responseBody['property']['additionalComments'] = null;
		$responseBody['property']['valueTypes'] = [];
		$responseBody['property']['ownerInterest'] = null;
		$responseBody['property']['ownerInterests'] = [];
		$responseBody['property']['valueQualifiers'] = [];
		$responseBody['property']['contacts'] = null;
		$responseBody['property']['legal'] = null;
		$responseBody['loanType'] = null;
		$responseBody['loanAmount'] = null;
		$responseBody['amcLicenseNumber'] = null;
		$responseBody['amcLicenseExpiresAt'] = null;
		$responseBody['additionalDocuments'] = [];
		$responseBody['instructionDocuments'] = [];
		$responseBody['approachesToBeIncluded'] = [];
		$responseBody['property']['contacts'] = [];
		$responseBody['instruction'] = null;

		$responseBody['property']['bestPersonToContact'] = 'borrower';

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'includes' => $includes,
				'auth' => 'customer',
				'body' => $requestBody
			],
			'response' => [
				'body' => $responseBody
			]
		];
	},


	'tryUpdateAdditionalJobTypes' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'additionalJobTypes' => [10, 22, 10000, 18]
				]
			],
			'response' => [
				'errors' => [
					'additionalJobTypes' => [
						'identifier' => 'not-belong',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'updateTheSameAdditionalJobTypes' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'additionalJobTypes' => [11, 12, 13]
				]
			]
		];
	},

	'getTheSameAdditionalJobTypes' => function(Runtime $runtime) use ($includes){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'includes' => ['additionalJobTypes'],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'additionalJobTypes' => new Dynamic(function($value){
						return array_equal([11, 12, 13], array_map(function($row){
							return $row['id'];
						}, $value));
					}),
				]
			]
		];
	},

	'createAdditionalDocument1:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('create.id').'/additional-documents',
				'auth' => 'customer',
				'body' => [
					'label' => 'Contract Document #2',
					'document' => [
						'id' => $runtime->getCapture()->get('createContractDocument2.id'),
						'token' => $runtime->getCapture()->get('createContractDocument2.token')
					]
				]
			],
		];
	},

	'update' => function(Runtime $runtime) use ($freezer){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = $freezer->get('requestUpdatedBody', $runtime->getHelper());
		$data['contractDocument'] = $capture->get('createAdditionalDocument1.id');

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => $data
			],
			'live' => function(Runtime $runtime){

				$capture = $runtime->getCapture();

				return [
					'body' => [
						[
                            'channels' => [
                                'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                                'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                            ],
							'event' => 'order:create-log',
							'data' => new Dynamic(function($data){
								return $data['action'] === Action::UPDATE_ORDER;
							})
						],
						[
                            'channels' => [
                                'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                                'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                            ],
							'event' => 'order:update',
							'data' => [
								'id' => $capture->get('create.id'),
								'fileNumber' => new Dynamic(Dynamic::STRING),
								'processStatus' => new Dynamic(Dynamic::STRING)
							]
						],
					]
				];
			},

			'emails' => function(Runtime $runtime) use ($data){
				$session = $runtime->getSession('appraiser');

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($data){
								return starts_with($value, 'Updated - Order on '.array_get($data, 'property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			},

			'mobile' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return [
					'body' => [
						[
							'users' => [$session->get('user.id')],
							'notification' => [
								'category' => 'order',
								'name' => 'update'
							],
							'message' => new Dynamic(function($value) use ($capture){
								$property = $capture->get('create.property');

								return str_contains($value, [
									'has updated the order',
									$property['address1'].', '.$property['city'].', '.$property['state']['code'].' '.$property['zip']
								]);
							}),
							'extra' => [
								'order' => $capture->get('create.id')
							]
						]
					]
				];
			}
		];
	},

	'getUpdateLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		$capture = $runtime->getCapture();
		$order = $capture->get('create');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$order['id'].'/logs',
				'parameters' => [
					'perPage' => 1000
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_ORDER,
					'message' => sprintf(
						'%s has updated the order on %s, %s, %s %s.',
						$customerSession->get('user.name'),
						$order['property']['address1'],
						$order['property']['city'],
						$order['property']['state']['code'],
						$order['property']['zip']
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'customer' => $order['customer']['name'],
						'address1' => $capture->get('create.property.address1'),
						'address2' => $capture->get('create.property.address2'),
						'city' => $capture->get('create.property.city'),
						'zip' => $capture->get('create.property.zip'),
						'state' => $capture->get('create.property.state'),
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::UPDATE_ORDER;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getUpdated' => function(Runtime $runtime) use ($includes, $freezer){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();


		$requestUpdatedBody = $freezer->get('requestUpdatedBody', $runtime->getHelper());

		$body = $requestUpdatedBody;

		$body['assignedAt'] = new Dynamic(Dynamic::DATETIME);

		$body['id'] = $capture->get('create.id');
		$body['isPaid'] = false;


		$body['jobType'] = [
			'id' => $requestUpdatedBody['jobType'],
			'isCommercial' => false,
			'isPayable' => true,
			'title' => new Dynamic(Dynamic::STRING),
			'local' => new Dynamic(function($v){
				return is_array($v);
			})
		];

		$body['additionalJobTypes'] = new Dynamic(function($value){
			return array_equal([13, 20], array_map(function($row){ return $row['id']; }, $value));
		});

		$body['processStatus'] = 'new';
		$body['inspectionCompletedAt'] = null;
		$body['estimatedCompletionDate'] = null;
		$body['completedAt'] = null;
		$body['paidAt'] = null;
		$body['property']['state'] = [
			'code' => 'NV',
			'name' => 'Nevada'
		];
		$body['property']['county'] = [
			'id' => $requestUpdatedBody['property']['county'],
			'title' => new Dynamic(Dynamic::STRING)
		];

		$body['property']['latitude'] = null;
		$body['property']['longitude'] = null;
		$body['property']['displayAddress'] = '777 ggg Str., Las Vegas, NV 99744';

		$body['contractDocument'] = [
			'id' => new Dynamic(Dynamic::INT),
			'label' => 'Contract Document #2',
			'type' => null,
			'document' => $capture->get('createContractDocument2'),
			'createdAt' => new Dynamic(Dynamic::DATETIME)
		];

		$body['customer'] = new Dynamic(function($data){
			return is_array($data);
		});

		$body['client'] = [
			'id' => new Dynamic(Dynamic::INT),
			'name' => 'Good Client 1',
			'address1' => '4th Street',
			'address2' => '5th Street',
			'city' => 'San Ocean',
			'state' => [
				'code' => 'CA',
				'name' => 'California'
			],
			'zip' => '94132',
		];

		foreach ($body['client'] as $key => $value){

			if ($key == 'id'){
				continue ;
			}

			$body['client'.ucfirst($key)] = $value;
		}

		$body['clientDisplayedOnReport'] = [
			'id' => new Dynamic(Dynamic::INT),
			'name' => 'Best Client 1',
			'address1' => '6th Street',
			'address2' => '7th Street',
			'city' => 'Los Food',
			'state' => [
				'code' => 'TX',
				'name' => 'Texas'
			],
			'zip' => '88888',
		];

		foreach ($body['clientDisplayedOnReport'] as $key => $value){

			if ($key == 'id'){
				continue ;
			}

			$body['clientDisplayedOnReport'.ucfirst($key)] = $value;
		}

		$body['property']['contacts'][0]['displayName'] = 'Test 2 & Co';
		$body['property']['contacts'][0]['intentProceedDate'] = null;

        $body['property']['ownerInterest'] = $body['property']['ownerInterests'][0];

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'includes' => $includes,
				'auth' => 'customer'
			],
			'response' => [
				'body' => $body
			]
		];
	},

	'updateWithUnset' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = [];

		$data['additionalJobTypes'] = [];
		$data['referenceNumber'] = null;
		$data['techFee'] = null;
		$data['purchasePrice'] = null;
		$data['fhaNumber'] = null;
		$data['loanNumber'] = null;
		$data['contractDocument'] = null;
		$data['contractDate'] = null;
		$data['salesPrice'] = null;
		$data['concession'] = null;
		$data['concessionUnit'] = null;
		$data['dueDate'] = null;
        $data['property']['characteristics'] = [];
		$data['property']['approxBuildingSize'] = null;
		$data['property']['approxLandSize'] = null;
		$data['property']['buildingAge'] = null;
		$data['property']['numberOfStories'] = null;
		$data['property']['numberOfUnits'] = null;
		$data['property']['grossRentalIncome'] = null;
		$data['property']['incomeSalesCost'] = null;
		$data['property']['address2'] = null;
		$data['property']['additionalComments'] = null;
		$data['property']['valueTypes'] = [];
		$data['property']['ownerInterest'] = null;
		$data['property']['valueQualifiers'] = [];
		$data['property']['contacts'] = [];
		$data['property']['legal'] = null;
		$data['loanType'] = null;
		$data['loanAmount'] = null;
		$data['amcLicenseNumber'] = null;
		$data['amcLicenseExpiresAt'] = null;
		$data['additionalDocuments'] = [];
		$data['approachesToBeIncluded'] = [];
		$data['instructionDocuments'] = [];
		$data['instruction'] = null;

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'getUpdatedWithUnset' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = [];

		$data['additionalJobTypes'] = [];
		$data['referenceNumber'] = null;
		$data['techFee'] = null;
		$data['purchasePrice'] = null;
		$data['fhaNumber'] = null;
		$data['loanNumber'] = null;
		$data['contractDocument'] = null;
		$data['contractDate'] = null;
		$data['salesPrice'] = null;
		$data['concession'] = null;
		$data['concessionUnit'] = null;
		$data['dueDate'] = null;
        $data['property']['characteristics'] = [];
		$data['property']['approxBuildingSize'] = null;
		$data['property']['approxLandSize'] = null;
		$data['property']['buildingAge'] = null;
		$data['property']['numberOfStories'] = null;
		$data['property']['numberOfUnits'] = null;
		$data['property']['grossRentalIncome'] = null;
		$data['property']['incomeSalesCost'] = null;
		$data['property']['address2'] = null;
		$data['property']['additionalComments'] = null;
		$data['property']['valueTypes'] = [];
		$data['property']['ownerInterest'] = null;
		$data['property']['valueQualifiers'] = [];
		$data['property']['contacts'] = [];
		$data['property']['legal'] = null;
		$data['loanType'] = null;
		$data['loanAmount'] = null;
		$data['amcLicenseNumber'] = null;
		$data['amcLicenseExpiresAt'] = null;
		$data['additionalDocuments'] = [];
		$data['instructionDocuments'] = [];
		$data['approachesToBeIncluded'] = [];
		$data['instruction'] = null;

		$includes = array_keys(array_smash($data));

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'includes' => $includes,
				'auth' => 'customer'
			],
			'response' => [
				'body' => $data,
				'filter' => new ItemFieldsFilter($includes, true)
			]
		];
	},

	'tryClearMarkAsPaid1' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'isPaid' => null
				]
			],
			'response' => [
				'errors' => [
					'isPaid' => [
						'identifier' => 'not-clearable',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'tryMarkAsPaid1' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'isPaid' => true
				]
			],
			'response' => [
				'errors' => [
					'paidAt' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'markAsPaid' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'isPaid' => true,
					'paidAt' => (new DateTime('-1 day'))->format(DateTime::ATOM)
				]
			]
		];
	},

	'getMarkedAsPaidAfter' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('create.id'),
				'includes' => ['isPaid', 'paidAt'],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => $capture->get('create.id'),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'isPaid' => true,
					'paidAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},

	'createForDelete:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'includes' => ['property', 'processStatus', 'customer'],
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'createPdf:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],
	'createDocument:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createForDelete.id').'/documents',
				'auth' => 'customer',
				'body' => [
					'primary' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			]
		];
	},
	'getCreatedDocument' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createForDelete.id').'/documents',
				'auth' => 'customer',
			],
			'response' => [
				'total' => 1
			]
		];
	},
	'createAdditionalDocument:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createForDelete.id').'/additional-documents',
				'auth' => 'customer',
				'body' => [
					'type' => null,
					'label' => 'Test Label',
					'document' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			]
		];
	},
	'getCreatedAdditionalDocument' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createForDelete.id').'/additional-documents',
				'auth' => 'customer'
			],
			'response' => [
				'total' => 1
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/orders/'.$capture->get('createForDelete.id'),
				'auth' => 'customer'
			],
			'raw' => function(PHPUnit_Framework_TestCase $test, EntityManagerInterface $entityManager) use ($capture){
				$document = $entityManager->find(Document::class, $capture->get('createDocument.id'));
				$test->assertTrue($document === null, 'The document is still in the database.');

				$document = $entityManager->find(AdditionalDocument::class, $capture->get('createAdditionalDocument.id'));
				$test->assertTrue($document === null, 'The additional document is still in the database.');
			},
			'live' => function(Runtime $runtime){
				return [
					'body' => [
						[
                            'channels' => [
                                'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                                'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                            ],
							'event' => 'order:create-log',
							'data' => new Dynamic(function($data){
								return $data['action'] === Action::DELETE_ORDER;
							})
						],
						[
                            'channels' => [
                                'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                                'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                            ],
							'event' => 'order:delete',
							'data' => [
								'id' => $runtime->getCapture()->get('createForDelete.id'),
								'fileNumber' => $runtime->getCapture()->get('createForDelete.fileNumber'),
								'processStatus' => $runtime->getCapture()->get('createForDelete.processStatus')
							]
						],
					]
				];
			},

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Deleted - Order on '.$capture->get('create.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			},

			'mobile' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return [
					'body' => [
						[
							'users' => [$session->get('user.id')],
							'notification' => [
								'category' => 'order',
								'name' => 'delete'
							],
							'message' => new Dynamic(function($value) use ($capture){
								$property = $capture->get('create.property');

								return str_contains($value, [
									'has deleted the order',
									$property['address1'].', '.$property['city'].', '.$property['state']['code'].' '.$property['zip']
								]);
							}),
							'extra' => [
								'order' => $capture->get('createForDelete.id'),
								'fileNumber' => $capture->get('createForDelete.fileNumber'),
								'processStatus' => $capture->get('createForDelete.processStatus')
							]
						]
					]
				];
			}
		];
	},

	'getDeleteLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$order = $capture->get('createForDelete');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
					'orderBy' => 'createdAt:desc'
				]
			],
			'response' => [
				'body' => [
					'action' => Action::DELETE_ORDER,
					'message' => sprintf(
						'%s has deleted the order on %s, %s, %s %s.',
						$customerSession->get('user.name'),
						$order['property']['address1'],
						$order['property']['city'],
						$order['property']['state']['code'],
						$order['property']['zip']
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'customer' => $order['customer']['name'],
						'address1' => $order['property']['address1'],
						'address2' => $order['property']['address2'],
						'city' => $order['property']['city'],
						'zip' => $order['property']['zip'],
						'state' => $order['property']['state']
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::DELETE_ORDER;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'tryDeleteAgain' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/orders/'.$capture->get('createForDelete.id'),
				'auth' => 'customer'
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},

	'validateRequiredWithConditions' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['acceptedConditions'] = [
			'fee' => -10
		];

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			],
			'response' => [
				'errors' => [
					'acceptedConditions.request' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'acceptedConditions.explanation' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'acceptedConditions.fee' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'createWithConditions' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['acceptedConditions'] = [
			'request' => AppraiserRequest::FEE_INCREASE,
			'fee' => 200.39,
			'explanation' => 'I need that',
			'additionalComments' => 'Test'
		];

		$acceptedConditions = $data['acceptedConditions'];
		$acceptedConditions['dueDate'] = null;

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'includes' => ['acceptedConditions'],
				'auth' => 'customer',
				'body' => $data
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'acceptedConditions' => $acceptedConditions
				]
			]
		];
	},

    'addFdicWhenUpdate' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /customers/'.$runtime->getSession('customer')->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createWithMinimum.id'),
                'auth' => 'customer',
                'body' => [
                    'fdic' => [
                        'fin' => '44221'
                    ]
                ]
            ]
        ];
    },
    'getWithFdic' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getSession('customer')->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createWithMinimum.id'),
                'auth' => 'customer',
                'includes' => ['fdic'],
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'fileNumber' => new Dynamic(Dynamic::STRING),
                    'fdic' => [
                        'fin' => '44221',
                        'line' => null,
                        'assetNumber' => null,
                        'assetType' => null,
                        'contractor' => null,
                        'taskOrder' => null
                    ]
                ]
            ]
        ];
    },
    'removeFdicWhenUpdate' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /customers/'.$runtime->getSession('customer')->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createWithMinimum.id'),
                'auth' => 'customer',
                'body' => [
                    'fdic' => null
                ]
            ]
        ];
    },
    'getWithFdicUnset' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getSession('customer')->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createWithMinimum.id'),
                'auth' => 'customer',
                'includes' => ['fdic'],
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'fileNumber' => new Dynamic(Dynamic::STRING),
                    'fdic' => null
                ]
            ]
        ];
    },
];