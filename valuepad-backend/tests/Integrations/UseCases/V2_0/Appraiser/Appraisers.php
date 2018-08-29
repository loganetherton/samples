<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Core\Appraiser\Enums\BusinessType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Enums\CommercialExpertise;
use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Tests\Integrations\Support\Filters\MessageAndExtraFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\User\Enums\Status;


$commons = [
	'update.expiresAt' => (new DateTime('+2 month'))->format('c'),
	'update.eoExpiresAt' => (new DateTime('+2 month'))->format('c'),

	'includes' => [
		'availability',
		'companyName',
		'businessTypes',
		'companyType',
		'otherCompanyType',
		'taxIdentificationNumber',
		'w9',
		'company',
		'address1',
		'address2',
		'city',
		'state',
		'zip',
		'assignmentAddress1',
		'assignmentAddress2',
		'assignmentCity',
		'assignmentState',
		'assignmentZip',
		'phone',
		'cell',
		'fax',
		'sampleReports',
		'languages',
		'eo',
		'qualifications',
		'signature',
		'signedAt'
	]
];

$update =  [
	'availability' => [
		'isOnVacation' => true,
		'from' => (new DateTime('-10 days'))->format(DateTime::ATOM),
		'to' => (new DateTime('+ 10 days'))->format(DateTime::ATOM),
		'message' => 'Testing...'
	],
	'companyName' => 'Test Company',
	'businessTypes' => [BusinessType::WOMEN_OWNED_BUSINESS],
	'companyType' => CompanyType::PARTNERSHIP,
	'otherCompanyType' => 'updated custom company type',
	'taxIdentificationNumber' => '87-9999333',
	'username' => 'updatetestappraiser',
	'firstName' => 'Will',
	'lastName' => 'Smith',
	'email' => 'will.smith@test.org',
	'languages' => ['fra', 'deu'],
	'address1' => '111 Holloway ave',
	'address2' => '222 Holloway ave',
	'city' => 'Oakland',
	'state' => 'FL',
	'zip' => '94102',
	'assignmentAddress1' => '666 Holloway ave',
	'assignmentAddress2' => '13 Holloway ave',
	'assignmentCity' => 'New York',
	'assignmentState' => 'NY',
	'assignmentZip' => '20001',
	'phone' => '(555) 777-9999',
	'cell' => '(666) 777-0003',
	'fax' => '(234) 111-5555',
	'qualifications' => [
		'primaryLicense' => [
			'expiresAt' => $commons['update.expiresAt'],
			'certifications' => ['certified-residential'],
			'isFhaApproved' => false,
			'isCommercial' => false,
			'coverage' => [
				[
					'county' => null, // need to replace
					'zips' => ['75559', '75570', '75599']
				],
				[
					'county' => null // need to replace
				]
			],
			'document' => null
		],
		'yearsLicensed' => 4,
		'certifiedAt' => [
			'month' => 10,
			'year' => 2002
		],
		'vaQualified' => false,
		'fhaQualified' => false,
		'relocationQualified' => false,
		'usdaQualified' => true,
		'coopQualified' => false,
		'jumboQualified' => true,
		'newConstructionQualified' => false,

		'newConstructionExperienceInYears' => 45,
		'numberOfNewConstructionCompleted' => 22,
		'isNewConstructionCourseCompleted' => false,
		'isFamiliarWithFullScopeInNewConstruction' => true,

		'loan203KQualified' => false,
		'manufacturedHomeQualified' => false,
		'reoQualified' => false,
		'deskReviewQualified' => false,
		'fieldReviewQualified' => false,
		'envCapable' => false,
		'commercialQualified' => false,
		'commercialExpertise' => [CommercialExpertise::OTHER, CommercialExpertise::MULTI_FAMILY],
		'otherCommercialExpertise' => 'some stuff',
	],
	'eo' => [
		'claimAmount' => 444.09,
		'aggregateAmount' => 2,
		'expiresAt' => (new DateTime('+4 month'))->format('c'),
		'carrier' => 'different',
		'deductible' => 331,
		'question1' => true,
		'question1Explanation' => 'Explanation Updated #1',
		'question2' => false,
		'question2Explanation' => 'Explanation Updated #2',
		'question3' => true,
		'question3Explanation' => 'Explanation Updated #3',
		'question4' => false,
		'question4Explanation' => 'Explanation Updated #4',
		'question5' => false,
		'question5Explanation' => 'Explanation Updated #5',
		'question6' => true,
		'question6Explanation' => 'Explanation Updated #6',
		'question7' => false,
		'question7Explanation' => 'Explanation Updated #7',
	],
	'signature' => 'Will Smith',
	'signedAt' => (new DateTime('-9 days'))->format(DateTime::ATOM),
	'sampleReports' => []
];

return [
    'createResume1:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],
	'createResume2:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],
	'createW91:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],
	'createW92:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],
    'createEoDocument1:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],
	'createEoDocument2:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],
    'createSampleReport1:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],
    'createSampleReport2:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],
    'createSampleReport3:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],
	'createPrimaryLicenseDocument:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],
	'createQuestion1Document1:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],
	'createQuestion1Document2:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],

	'validateRequired' => function(){

		$fields = [
			'firstName', 'lastName', 'email', 'username', 'password',
			'availability.from', 'availability.to',
			'companyName', 'businessTypes', 'companyType', 'taxIdentificationNumber', 'w9',
			'languages', 'address1', 'city', 'state', 'zip', 'assignmentAddress1',
			'assignmentState', 'assignmentCity', 'assignmentZip', 'phone', 'cell',
			'qualifications.primaryLicense.number', 'qualifications.primaryLicense.state',
			'qualifications.primaryLicense.expiresAt', 'qualifications.primaryLicense.certifications',
			'qualifications.newConstructionExperienceInYears', 'qualifications.numberOfNewConstructionCompleted',
			'qualifications.isNewConstructionCourseCompleted', 'qualifications.isFamiliarWithFullScopeInNewConstruction',
			'eo.document', 'eo.claimAmount', 'eo.aggregateAmount', 'eo.expiresAt',
			'signature', 'signedAt', 'qualifications.yearsLicensed', 'qualifications.commercialExpertise'
		];

		$errors = [];


		foreach ($fields as $field){
			$errors[$field] = [
				'identifier' => 'required',
				'message' => new Dynamic(Dynamic::STRING),
				'extra' => []
			];
		}

		for ($i = 1; $i <= 7; $i++){
			$errors['eo.question'.$i] = [
				'identifier' => 'required',
				'message' => new Dynamic(Dynamic::STRING),
				'extra' => []
			];
		}

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'body' => [
					'qualifications' => [
						'newConstructionQualified' => true,
						'commercialQualified' => true
					],
					'availability' => [
						'isOnVacation' => true
					]
				]
			],
			'response' => [
				'errors' => $errors
			]
		];
	},

	'validateRequiredBasedOnValues' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => 'newappraisertest',
			'password' => 'password',
			'w9' => [
				'id' => $capture->get('createW91.id'),
				'token' => $capture->get('createW91.token')
			],
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'CCCXXX10',
					'state' => 'TX',
				]
			],
			'eo' => [
				'document' => [
					'id' => $capture->get('createEoDocument1.id'),
					'token' => $capture->get('createEoDocument1.token')
				]
			]
		]);

		$data['qualifications']['primaryLicense']['certifications']
			= [Certification::CERTIFIED_RESIDENTIAL, Certification::CERTIFIED_GENERAL];

		$data['qualifications']['commercialExpertise'] = [CommercialExpertise::HOSPITALITY, CommercialExpertise::OTHER];

		for ($i = 1; $i <= 7; $i++){
			$data['eo']['question'.$i] = true;
			unset($data['eo']['question'.$i.'Explanation']);
		}

		unset($data['qualifications']['certifiedAt']);
		unset($data['qualifications']['commercialQualified']);
		unset($data['qualifications']['otherCommercialExpertise']);

		$errors = [
			'qualifications.certifiedAt' => [
				'identifier' => 'required',
				'message' => new Dynamic(Dynamic::STRING),
				'extra' => []
			],
			'qualifications.commercialQualified' => [
				'identifier' => 'required',
				'message' => new Dynamic(Dynamic::STRING),
				'extra' => []
			],
			'qualifications.otherCommercialExpertise' => [
				'identifier' => 'required',
				'message' => new Dynamic(Dynamic::STRING),
				'extra' => []
			]
		];

		for ($i = 1; $i <= 7; $i++){
			$errors['eo.question'.$i.'Explanation'] = [
				'identifier' => 'required',
				'message' => new Dynamic(Dynamic::STRING),
				'extra' => []
			];
		}

		$errors['eo.question1Document'] = [
			'identifier' => 'required',
			'message' => new Dynamic(Dynamic::STRING),
			'extra' => []
		];

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'body' => $data,
			],
			'response' => [
				'errors' => $errors
			]
		];

	},

    'validate' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

		$data = [
			'firstName' => ' ',
			'lastName' => ' ',
			'username' => '11',
			'password' => '22',
			'email' => 'jimmy.fallon&test.org',
			'companyName' => ' ',
			'businessTypes' => [],
			'taxIdentificationNumber' => '14123fadf123',
			'languages' => ['uuu', 'sss'],
			'address1' => ' ',
			'address2' => ' ',
			'city' => ' ',
			'state' => 'DD',
			'zip' => '33',
			'assignmentAddress1' => ' ',
			'assignmentAddress2' => ' ',
			'assignmentCity' => ' ',
			'assignmentState' => 'FF',
			'assignmentZip' => '777',
			'phone' => '(222242-1212',
			'cell' => '(222)2421212',
			'fax' => '222 242-1212',
			'availability' => [
				'isOnVacation' => true,
				'from' => (new DateTime('+10 days'))->format(DateTime::ATOM),
				'to' => (new DateTime('+5 days'))->format(DateTime::ATOM)
			],
			'qualifications' => [
				'primaryLicense' => [
					'number' => ' ',
					'expiresAt' => (new DateTime('-1 months'))->format('c'),
					'state' => 'GG',
					'certifications' => []
				],
				'yearsLicensed' => -109,
				'certifiedAt' => [
					'month' => 12,
					'year' => 1902
				],
				'otherCommercialExpertise' => ' ',
				'resume' => 99999,
				'newConstructionExperienceInYears' => -10,
				'numberOfNewConstructionCompleted' => -2,
			],
			'eo' => [
				'question1Document' => 1412,
				'claimAmount' => -100.09,
				'aggregateAmount' => -100.01,
				'expiresAt' => (new DateTime('-11 months'))->format('c'),
				'carrier' => ' ',
				'document' => [
					'id' => $capture->get('createEoDocument1.id'),
					'token' => 'dummy and wrong'
				],
				'deductible' => -221.92
			],
			'sampleReports' => [
				[
					'id' => $capture->get('createSampleReport1.id'),
					'token' => $capture->get('createSampleReport1.token')
				],
				$capture->get('createSampleReport2.id'),
				$capture->get('createSampleReport3.id'),
			],
			'signature' => ' '
		];

		for($i = 1; $i <= 7; $i++){
			$data['eo']['question'.$i.'Explanation'] = ' ';
		}

		$errors = [
			'firstName' => [
				'identifier' => 'empty'
			],
			'lastName' => [
				'identifier' => 'empty'
			],
			'username' => [
				'identifier' => 'format'
			],
			'password' => [
				'identifier' => 'format'
			],
			'email' => [
				'identifier' => 'format'
			],
			'availability.from' => [
				'identifier' => 'invalid'
			],
			'companyName' => [
				'identifier' => 'empty'
			],
			'businessTypes' => [
				'identifier' => 'empty'
			],
			'taxIdentificationNumber' => [
				'identifier' => 'format'
			],
			'languages' => [
				'identifier' => 'exists'
			],
			'address1' => [
				'identifier' => 'empty'
			],
			'address2' => [
				'identifier' => 'empty'
			],
			'city' => [
				'identifier' => 'empty'
			],
			'zip' => [
				'identifier' => 'format'
			],
			'state' => [
				'identifier' => 'exists'
			],
			'assignmentAddress1' => [
				'identifier' => 'empty'
			],
			'assignmentAddress2' => [
				'identifier' => 'empty'
			],
			'assignmentCity' => [
				'identifier' => 'empty'
			],
			'assignmentZip' => [
				'identifier' => 'format'
			],
			'assignmentState' => [
				'identifier' => 'exists'
			],
			'phone' => [
				'identifier' => 'format'
			],
			'cell' => [
				'identifier' => 'format'
			],
			'fax' => [
				'identifier' => 'format'
			],
			'qualifications.certifiedAt' => [
				'identifier' => 'format'
			],
			'qualifications.primaryLicense.number' => [
				'identifier' => 'empty'
			],
			'qualifications.primaryLicense.state' => [
				'identifier' => 'exists'
			],
			'qualifications.yearsLicensed' => [
				'identifier' => 'greater'
			],
			'qualifications.primaryLicense.expiresAt' => [
				'identifier' => 'greater'
			],
			'qualifications.resume' => [
				'identifier' => 'exists'
			],
			'qualifications.otherCommercialExpertise' => [
				'identifier' => 'empty'
			],
			'qualifications.newConstructionExperienceInYears' => [
				'identifier' => 'greater'
			],
			'qualifications.numberOfNewConstructionCompleted' => [
				'identifier' => 'greater'
			],
			'eo.document' => [
				'identifier' => 'permissions'
			],
			'eo.question1Document' => [
				'identifier' => 'exists'
			],
			'eo.claimAmount' => [
				'identifier' => 'greater'
			],
			'eo.aggregateAmount' => [
				'identifier' => 'greater'
			],
			'eo.deductible' => [
				'identifier' => 'greater'
			],
			'eo.expiresAt' => [
				'identifier' => 'greater'
			],
			'eo.carrier' => [
				'identifier' => 'empty'
			],
			'qualifications.primaryLicense.certifications' => [
				'identifier' => 'empty'
			],
			'sampleReports' => [
				'identifier' => 'permissions'
			],
			'signature' => [
				'identifier' => 'empty'
			]
		];

		for($i = 1; $i <= 7; $i++){
			$errors['eo.question'.$i.'Explanation'] = [
				'identifier' => 'empty'
			];
		}

		$fields = $data;

		unset(
			$fields['languages'],
			$fields['sampleReports'],
			$fields['qualifications']['certifiedAt'],
			$fields['eo']['document']
		);

		$fields = array_keys(array_smash($data));

		$fields[] = 'languages';
		$fields[] = 'sampleReports';
		$fields[] = 'qualifications.certifiedAt';
		$fields[] = 'eo.document';

        return [
            'request' => [
                'url' => 'POST /appraisers',
                'body' => $data
            ],
            'response' => [
                'errors' => $errors,
                'filter' => new CompositeFilter([
					new ItemFieldsFilter($fields, true),
					new MessageAndExtraFilter()
				])
            ]
        ];
    },


	'createAppraiserWithMinimumInfo' => function(Runtime $runtime) use ($commons){

		$capture = $runtime->getCapture();

		$data = [
			'firstName' => 'Anything',
			'lastName' => 'Possible',
			'email' => 'anything.possible@test.org',
			'username' => 'anything.possible@test.org',
			'password' => 'password',
			'companyName' => 'Some weird name',
			'businessTypes' => [BusinessType::HUN_ZONE_SMALL_BUSINESS],
			'companyType' => CompanyType::LLC_C,
			'taxIdentificationNumber' => '333-22-4422',
			'w9' => [
				'id' => $capture->get('createW91.id'),
				'token' =>  $capture->get('createW91.token'),
			],
			'languages' => ['eng'],
			'address1' => '11 High Hills',
			'city' => 'Las Vegas',
			'state' => 'NV',
			'zip' => '65333',
			'assignmentAddress1' => '11 High Hills',
			'assignmentCity' => 'Las Vegas',
			'assignmentState' => 'NV',
			'assignmentZip' => '65333',
			'phone' => '(666) 633-2222',
			'cell' => '(939) 232-1121',
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'dummy',
					'state' => 'TX',
					'expiresAt' => (new DateTime('+ 1 year'))->format(DateTime::ATOM),
					'certifications' => [Certification::LICENSED]
				],
				'yearsLicensed' => 10,
			],
			'eo' => [
				'document' => [
					'id' => $capture->get('createEoDocument1.id'),
					'token' => $capture->get('createEoDocument1.token')
				],
				'claimAmount' => 120.33,
				'aggregateAmount' => 33.93,
				'expiresAt' => (new DateTime('+ 1 year'))->format(DateTime::ATOM),
				'carrier' => 'test'
			],
			'signature' => 'Anything',
			'signedAt' => (new DateTime())->format(DateTime::ATOM)

		];

		for ($i = 1; $i <=7; $i++){
			$data['eo']['question'.$i] = false;
		}

		$result = $data;

		$result['id'] = new Dynamic(Dynamic::INT);

		$result['w9'] = $capture->get('createW91');
		$result['languages'] = [[
			'code' => 'eng',
			'name' => 'English'
		]];

		$result['state'] = [
			'code' => 'NV',
			'name' => 'Nevada'
		];

		$result['assignmentState'] = [
			'code' => 'NV',
			'name' => 'Nevada'
		];

		$result['qualifications']['primaryLicense'] = [
			'id' => new Dynamic(Dynamic::INT),
			'isPrimary' => true,
			'number' => new Dynamic(Dynamic::STRING),
			'state' => [
				'code' => 'TX',
				'name' => 'Texas'
			],
			'certifications' => $data['qualifications']['primaryLicense']['certifications'],
			'expiresAt' => $data['qualifications']['primaryLicense']['expiresAt'],
			'coverage' => [],
			'document' => null,
			'isFhaApproved' => false,
			'isCommercial' => false
		];

		$result['eo']['document'] = $capture->get('createEoDocument1');

		$result['availability'] = [
			'isOnVacation' => false,
			'from' => null,
			'to' => null,
			'message' => null
		];
		$result['fax'] = null;
		$result['otherCompanyType'] = null;
		$result['address2'] = null;
		$result['assignmentAddress2'] = null;
		$result['qualifications']['certifiedAt'] = null;
		$result['qualifications']['vaQualified'] = null;
		$result['qualifications']['fhaQualified'] = null;
		$result['qualifications']['relocationQualified'] = null;
		$result['qualifications']['usdaQualified'] = null;
		$result['qualifications']['coopQualified'] = null;
		$result['qualifications']['jumboQualified'] = null;
		$result['qualifications']['newConstructionQualified'] = null;
		$result['qualifications']['newConstructionExperienceInYears'] = null;
		$result['qualifications']['numberOfNewConstructionCompleted'] = null;
		$result['qualifications']['isNewConstructionCourseCompleted'] = null;
		$result['qualifications']['isFamiliarWithFullScopeInNewConstruction'] = null;
		$result['qualifications']['loan203KQualified'] = null;
		$result['qualifications']['manufacturedHomeQualified'] = null;
		$result['qualifications']['reoQualified'] = null;
		$result['qualifications']['deskReviewQualified'] = null;
		$result['qualifications']['fieldReviewQualified'] = null;
		$result['qualifications']['envCapable'] = null;
		$result['qualifications']['commercialQualified'] = null;
		$result['qualifications']['commercialExpertise'] = [];
		$result['qualifications']['otherCommercialExpertise'] = null;
		$result['qualifications']['resume'] = null;
		$result['eo']['deductible'] = null;
		$result['eo']['question1Document'] = null;
		$result['displayName'] = $result['firstName'].' '.$result['lastName'];
        $result['type'] = 'appraiser';

		for ($i = 1; $i <= 7; $i++){
			$result['eo']['question'.$i.'Explanation'] = null;
		}

		$result['sampleReports'] = [];

		unset($result['password']);


		return [
			'request' => [
				'url' => 'POST /appraisers',
				'body' => $data,
				'includes' => $commons['includes']
			],
			'response' => [
				'body' => $result
			]
		];
	},

	'loginAppraiserWithMinimumInfo:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'auth' => 'guest',
			'body' => [
				'username' => 'anything.possible@test.org',
				'password' => 'password'
			]
		]
	],

	'getAppraiserWithMinimumInfo' => function(Runtime $runtime) use ($commons){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiserWithMinimumInfo.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiserWithMinimumInfo.token')
				],
				'includes' => $commons['includes']
			],
			'response' => [
				'body' => $capture->get('createAppraiserWithMinimumInfo')
			]
		];
	},

	'tryUpdateNewConstructionQualified' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiserWithMinimumInfo.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiserWithMinimumInfo.token')
				],
				'body' => [
					'qualifications' => [
						'newConstructionQualified' => true,
						'primaryLicense' => [
							'certifications' => [Certification::CERTIFIED_GENERAL, Certification::CERTIFIED_RESIDENTIAL]
						]
					]
				]
			],

			'response' => [
				'errors' => [
					'qualifications.newConstructionExperienceInYears' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.numberOfNewConstructionCompleted' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.isNewConstructionCourseCompleted' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.isFamiliarWithFullScopeInNewConstruction' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.certifiedAt' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.commercialQualified' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'tryUpdateCommercialQualified' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiserWithMinimumInfo.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiserWithMinimumInfo.token')
				],
				'body' => [
					'qualifications' => [
						'commercialQualified' => true
					]
				]
			],
			'response' => [
				'errors' => [
					'qualifications.commercialExpertise' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'tryUpdateCommercialExpertise' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiserWithMinimumInfo.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiserWithMinimumInfo.token')
				],
				'body' => [
					'qualifications' => [
						'commercialQualified' => true,
						'commercialExpertise' => [CommercialExpertise::OTHER]
					]
				]
			],
			'response' => [
				'errors' => [
					'qualifications.otherCommercialExpertise' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'partiallyUpdateAppraiserWithMinimumInfo' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiserWithMinimumInfo.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiserWithMinimumInfo.token')
				],
				'body' => [
					'qualifications' => [
						'newConstructionExperienceInYears' => 10,
						'numberOfNewConstructionCompleted' => 2,
						'isNewConstructionCourseCompleted' => true,
						'isFamiliarWithFullScopeInNewConstruction' => true,
						'certifiedAt' => [
							'month' => 1,
							'year' => 1950
						],
						'commercialQualified' => false
					]
				]
			]
		];
	},

	'updateNewConstructionQualified' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiserWithMinimumInfo.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiserWithMinimumInfo.token')
				],
				'body' => [
					'qualifications' => [
						'newConstructionQualified' => true,
						'primaryLicense' => [
							'certifications' => [Certification::CERTIFIED_GENERAL, Certification::CERTIFIED_RESIDENTIAL]
						]
					]
				]
			]
		];
	},

	'tryResetAppraiserWithMinimumInfo' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiserWithMinimumInfo.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiserWithMinimumInfo.token')
				],
				'body' => [
					'qualifications' => [
						'newConstructionExperienceInYears' => null,
						'numberOfNewConstructionCompleted' => null,
						'isNewConstructionCourseCompleted' => null,
						'isFamiliarWithFullScopeInNewConstruction' => null,
						'certifiedAt' => null,
						'commercialQualified' => null
					]
				]
			],
			'response' => [
				'errors' => [
					'qualifications.newConstructionExperienceInYears' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.numberOfNewConstructionCompleted' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.isNewConstructionCourseCompleted' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.isFamiliarWithFullScopeInNewConstruction' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.certifiedAt' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'qualifications.commercialQualified' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'create' => function(Runtime $runtime) use ($commons){
        $capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => 'newappraisertest',
			'password' => 'password',
			'w9' => [
				'id' => $capture->get('createW91.id'),
				'token' => $capture->get('createW91.token')
			],
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'CCCXXX10',
					'state' => 'TX'
				],
			],
			'eo' => [
				'document' => [
					'id' => $capture->get('createEoDocument1.id'),
					'token' => $capture->get('createEoDocument1.token')
				]
			]
		]);

		$data['qualifications']['primaryLicense']['document'] = [
			'id' => $capture->get('createPrimaryLicenseDocument.id'),
			'token' => $capture->get('createPrimaryLicenseDocument.token')
		];

		$data['qualifications']['primaryLicense']['coverage'] =  [
			[
				'county' => $runtime->getHelper()->county('BOWIE', 'TX'),
				'zips' => ['75505']
			],
			[
				'county' => $runtime->getHelper()->county('UPSHUR', 'TX'),
			]
		];

		$data['sampleReports'] = [
			[
				'id' => $capture->get('createSampleReport1.id'),
				'token' => $capture->get('createSampleReport1.token')
			],
			[
				'id' => $capture->get('createSampleReport2.id'),
				'token' => $capture->get('createSampleReport2.token')
			],
			[
				'id' => $capture->get('createSampleReport3.id'),
				'token' => $capture->get('createSampleReport3.token')
			]
		];

		$data['eo']['question1Document'] = [
			'id' => $capture->get('createQuestion1Document1.id'),
			'token' => $capture->get('createQuestion1Document1.token')
		];

		$data['qualifications']['resume'] = [
			'id' => $capture->get('createResume1.id'),
			'token' => $capture->get('createResume1.token')
		];

		$result = $data;

		$result['id'] = new Dynamic(Dynamic::INT);

		$result['availability'] = [
			'isOnVacation' => false,
			'from' => null,
			'to' => null,
			'message' => null
		];

		unset($result['password']);
		$result['address2'] = null;
		$result['assignmentAddress2'] = null;
		$result['fax'] = null;

		$result['w9'] = $capture->get('createW91');
		$result['state'] = [
			'code' => 'CA',
			'name' => 'California'
		];

		$result['assignmentState'] = [
			'code' => 'CA',
			'name' => 'California'
		];

		$result['qualifications']['primaryLicense']['state'] = [
			'code' => 'TX',
			'name' => 'Texas'
		];

		$result['qualifications']['resume'] = $capture->get('createResume1');
		$result['qualifications']['primaryLicense']['document'] = $capture->get('createPrimaryLicenseDocument');
		$result['qualifications']['primaryLicense']['id'] = new Dynamic(Dynamic::INT);
		$result['qualifications']['primaryLicense']['isPrimary'] = true;

		$result['qualifications']['primaryLicense']['coverage'] = [
			[
				'county' => [
					'id' => $runtime->getHelper()->county('BOWIE', 'TX'),
					'title' => 'BOWIE'
				],
				'zips' => ['75505'],
			],
			[
				'county' => [
					'id' => $runtime->getHelper()->county('UPSHUR', 'TX'),
					'title' => 'UPSHUR'
				],
				'zips' => [],
			]
		];

		$result['eo']['document'] = $capture->get('createEoDocument1');
		$result['eo']['question1Document'] = $capture->get('createQuestion1Document1');
		$result['sampleReports'] = [
			$capture->get('createSampleReport1'),
			$capture->get('createSampleReport2'),
			$capture->get('createSampleReport3')
		];

		$result['languages'] = [
			[
				'code' => 'rus',
				'name' => 'Russian'
			],
			[
				'code' => 'eng',
				'name' => 'English'
			]
		];

		$result['displayName'] = $result['firstName'].' '.$result['lastName'];
        $result['type'] = 'appraiser';
		
        return [
            'request' => [
                'url' => 'POST /appraisers',
                'includes' => $commons['includes'],
				'body' => $data
            ],
            'response' => [
                'body' => $result
            ]
        ];
    },

	'getAscAppraiser:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /asc',
				'parameters' => [
					'search' => [
						'licenseNumber' => $capture->get('create.qualifications.primaryLicense.number')
					],
					'filter' => [
						'licenseState' => $capture->get('create.qualifications.primaryLicense.state.code')
					]
				]
			]
		];
	},

	'loginAppraiser:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'auth' => 'guest',
			'body' => [
				'username' => 'newappraisertest',
				'password' => 'password'
			]
		]
	],

	'invite:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => $capture->get('getAscAppraiser.0.id')
				],
				'auth' => 'customer'
			]
		];

	},

	'acceptInvitation:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('create.id').'/invitations/'
					.$capture->get('invite.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
		];
	},

	'fakeSampleReportsUpdate' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'body' => [
					'sampleReports' => [
						$capture->get('createSampleReport1.id'),
						$capture->get('createSampleReport2.id'),
						$capture->get('createSampleReport3.id')
					]
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			]
		];
	},

	'update' => function(Runtime $runtime) use ($update){
		$capture = $runtime->getCapture();

		$update['qualifications']['primaryLicense']['coverage'][0]['county'] = $runtime->getHelper()->county('BOWIE', 'TX');
		$update['qualifications']['primaryLicense']['coverage'][1]['county'] = $runtime->getHelper()->county('KENDALL', 'TX');

		$data = $update;

		$data['w9'] =  [
			'id' => $capture->get('createW92.id'),
			'token' => $capture->get('createW92.token')
		];

		$data['qualifications']['resume'] =  [
			'id' => $capture->get('createResume2.id'),
			'token' => $capture->get('createResume2.token')
		];

		$data['eo']['document'] = [
			'id' => $capture->get('createEoDocument2.id'),
			'token' => $capture->get('createEoDocument2.token')
		];

		$data['eo']['question1Document'] = [
			'id' => $capture->get('createQuestion1Document2.id'),
			'token' => $capture->get('createQuestion1Document2.token')
		];

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'body' => $data,
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'push' => [
				'body' => [
                    [
                        'type' => 'appraiser',
                        'event' => 'update-license',
                        'appraiser' => new Dynamic(Dynamic::INT),
                        'license' =>  new Dynamic(Dynamic::INT)
                    ],
					[
						'type' => 'appraiser',
						'event' => 'update',
						'appraiser' => $capture->get('create.id')
					]
				]
			]
		];
	},

	'get' => function(Runtime $runtime) use ($commons, $update){
		$capture = $runtime->getCapture();

		$update['qualifications']['primaryLicense']['coverage'][0]['county'] = $runtime->getHelper()->county('BOWIE', 'TX');
		$update['qualifications']['primaryLicense']['coverage'][1]['county'] = $runtime->getHelper()->county('KENDALL', 'TX');

		$data = $update;

		$data['id'] = new Dynamic(Dynamic::INT);

		$data['w9'] = $capture->get('createW92');
		$data['state'] = [
			'code' => 'FL',
			'name' => 'Florida'
		];

		$data['assignmentState'] = [
			'code' => 'NY',
			'name' => 'New York'
		];

		$data['qualifications']['primaryLicense']['state'] = [
			'code' => 'TX',
			'name' => 'Texas'
		];

		$data['qualifications']['primaryLicense']['document'] = null;
		$data['qualifications']['primaryLicense']['id'] = new Dynamic(Dynamic::INT);
		$data['qualifications']['primaryLicense']['isPrimary'] = true;
		$data['qualifications']['primaryLicense']['number'] = $capture->get('create.qualifications.primaryLicense.number');
		$data['qualifications']['primaryLicense']['state'] = $capture->get('create.qualifications.primaryLicense.state');


		$data['qualifications']['primaryLicense']['coverage'] = [
			[
				'county' => [
					'id' => $runtime->getHelper()->county('BOWIE', 'TX'),
					'title' => 'BOWIE'
				],
				'zips' => ['75559', '75570', '75599'],
			],
			[
				'county' => [
					'id' => $runtime->getHelper()->county('KENDALL', 'TX'),
					'title' => 'KENDALL'
				],
				'zips' => [],
			]
		];

		$data['qualifications']['resume'] = $capture->get('createResume2');
		$data['eo']['document'] = $capture->get('createEoDocument2');
		$data['eo']['question1Document'] = $capture->get('createQuestion1Document2');
		$data['sampleReports'] = [];

		$data['languages'] = new Dynamic(function($value){
			$lang = ['fra', 'deu'];
			return is_array($value)
			&& count($value) === 2
			&& $value[0]['code'] !== $value[1]['code']
			&& in_array($value[0]['code'], $lang)
			&& in_array($value[1]['code'], $lang);
		});

		$data['displayName'] = $data['firstName'].' '.$data['lastName'];
        $data['type'] = 'appraiser';

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'includes' => $commons['includes']
			],
			'response' => [
				'body' => $data
			]
		];
	},

	'resetAppraiserToMinimumInfo' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		$data['qualifications']['primaryLicense'] = [
			'coverage' => [],
			'document' => null
		];

		$data['qualifications']['primaryLicense']['certifications'] = [Certification::LICENSED];

		$data['availability'] = [
			'isOnVacation' => false,
			'from' => null,
			'to' => null,
			'message' => null
		];
		$data['fax'] = null;
		$data['address2'] = null;
		$data['assignmentAddress2'] = null;
		$data['qualifications']['certifiedAt'] = null;
		$data['qualifications']['vaQualified'] = null;
		$data['qualifications']['fhaQualified'] = null;
		$data['qualifications']['relocationQualified'] = null;
		$data['qualifications']['usdaQualified'] = null;
		$data['qualifications']['coopQualified'] = null;
		$data['qualifications']['jumboQualified'] = null;
		$data['qualifications']['newConstructionQualified'] = null;
		$data['qualifications']['newConstructionExperienceInYears'] = null;
		$data['qualifications']['numberOfNewConstructionCompleted'] = null;
		$data['qualifications']['isNewConstructionCourseCompleted'] = null;
		$data['qualifications']['isFamiliarWithFullScopeInNewConstruction'] = null;
		$data['qualifications']['loan203KQualified'] = null;
		$data['qualifications']['manufacturedHomeQualified'] = null;
		$data['qualifications']['reoQualified'] = null;
		$data['qualifications']['deskReviewQualified'] = null;
		$data['qualifications']['fieldReviewQualified'] = null;
		$data['qualifications']['envCapable'] = null;
		$data['qualifications']['commercialQualified'] = false;
		$data['qualifications']['commercialExpertise'] = [];
		$data['qualifications']['otherCommercialExpertise'] = null;
		$data['qualifications']['resume'] = null;
		$data['eo']['deductible'] = null;
		$data['eo']['question1Document'] = null;

		for ($i = 1; $i <= 7; $i++) {
			$data['eo']['question'.$i] = false;
			$data['eo']['question'.$i.'Explanation'] = null;
		}

		$data['sampleReports'] = [];

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'body' => $data
			]
		];
	},

	'getAppraiserResetToMinimum' => function(Runtime $runtime) use ($commons){

		$capture = $runtime->getCapture();

		$data['qualifications']['primaryLicense'] = [
			'coverage' => [],
			'document' => null
		];

		$data['qualifications']['primaryLicense']['certifications'] = [Certification::LICENSED];

		$data['availability'] = [
			'isOnVacation' => false,
			'from' => null,
			'to' => null,
			'message' => null
		];
		$data['fax'] = null;
		$data['address2'] = null;
		$data['assignmentAddress2'] = null;
		$data['qualifications']['certifiedAt'] = null;
		$data['qualifications']['vaQualified'] = null;
		$data['qualifications']['fhaQualified'] = null;
		$data['qualifications']['relocationQualified'] = null;
		$data['qualifications']['usdaQualified'] = null;
		$data['qualifications']['coopQualified'] = null;
		$data['qualifications']['jumboQualified'] = null;
		$data['qualifications']['newConstructionQualified'] = null;
		$data['qualifications']['newConstructionExperienceInYears'] = null;
		$data['qualifications']['numberOfNewConstructionCompleted'] = null;
		$data['qualifications']['isNewConstructionCourseCompleted'] = null;
		$data['qualifications']['isFamiliarWithFullScopeInNewConstruction'] = null;
		$data['qualifications']['loan203KQualified'] = null;
		$data['qualifications']['manufacturedHomeQualified'] = null;
		$data['qualifications']['reoQualified'] = null;
		$data['qualifications']['deskReviewQualified'] = null;
		$data['qualifications']['fieldReviewQualified'] = null;
		$data['qualifications']['envCapable'] = null;
		$data['qualifications']['commercialQualified'] = false;
		$data['qualifications']['commercialExpertise'] = [];
		$data['qualifications']['otherCommercialExpertise'] = null;
		$data['qualifications']['resume'] = null;
		$data['eo']['deductible'] = null;
		$data['eo']['question1Document'] = null;

		for ($i = 1; $i <= 7; $i++){
			$data['eo']['question'.$i] = false;
			$data['eo']['question'.$i.'Explanation'] = null;
		}

		$data['sampleReports'] = [];

		$fields = $data;
		unset($fields['qualifications']['primaryLicense']['certifications']);

		$fields = array_keys(array_smash($data));
		$fields[] = 'qualifications.primaryLicense.certifications';

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'includes' => $commons['includes']
			],
			'response' => [
				'body' => $data,
				'filter' => new ItemFieldsFilter($fields, true)
			]
		];
	},

	'tryRemoveLanguages' => function(Runtime $runtime) use ($commons){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'body' => [
					'languages' => [],
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'errors' => [
					'languages' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'tryUnsetOnVacation' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'body' => [
					'availability' => [
						'isOnVacation' => null
					]
				]
			],
			'response' => [
				'errors' => [
					'availability.isOnVacation' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'updateAvailability:init' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'body' => [
					'availability' => [
						'isOnVacation' => true,
						'from' => (new DateTime('+5 days'))->format(DateTime::ATOM),
						'to' => (new DateTime('+6 days'))->format(DateTime::ATOM)
					]
				]
			],
			'response' => [
				'errors' => [
					'availability.isOnVacation' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'tryChangeToDate' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'body' => [
					'availability' => [
						'to' => (new DateTime('+4 days'))->format(DateTime::ATOM)
					]
				]
			],
			'response' => [
				'errors' => [
					'availability.from' => [
						'identifier' => 'invalid',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'tryChangeUnsetDates' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'body' => [
					'availability' => [
						'from' => null,
						'to' => null,
					]
				]
			],
			'response' => [
				'errors' => [
					'availability.from' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'availability.to' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'signinAppraiser' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'updatetestappraiser',
				'password' => 'password'
			]
		],
		'response' => [
			'status' => 200
		]
	],

	'disable' => function(Runtime $runtime){
		$appraiser = $runtime->getCapture()->get('create');

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$appraiser['id'],
				'auth' => 'admin',
				'body' => [
					'status' => Status::DISABLED
				]
			]
		];
	},

	'trySigninAppraiser' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'updatetestappraiser',
				'password' => 'password'
			]
		],
		'response' => [
			'status' => 422
		]
	]
];