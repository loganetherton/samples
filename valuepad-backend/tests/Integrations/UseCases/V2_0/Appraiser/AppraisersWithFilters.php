<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use ValuePad\Core\Asc\Enums\Certification;
use Ascope\QA\Support\Response;

$appraiser = uniqid('appraiser');

return [
	'createW9:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'createEoDocument:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],

	'createAppraiser:init' => function(Runtime $runtime) use ($appraiser){

		$capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => $appraiser,
			'password' => 'password',
			'w9' => [
				'id' => $capture->get('createW9.id'),
				'token' => $capture->get('createW9.token')
			],
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'dummy',
					'state' => 'NE'
				],
			],
			'eo' => [
				'document' => [
					'id' => $capture->get('createEoDocument.id'),
					'token' => $capture->get('createEoDocument.token')
				]
			]
		]);

		$data['qualifications']['primaryLicense']['certifications']
			= [Certification::LICENSED, Certification::TRANSITIONAL_LICENSE];

		$data['qualifications']['primaryLicense']['coverage'] = [
			[
				'county' => $runtime->getHelper()->county('CHASE', 'NE'),
				'zips' => ['69033', '69023', '69045']
			]
		];

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'body' => $data
			]
		];
	},

	'createAppraiser2:init' => function(Runtime $runtime) use ($appraiser){

		$capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => uniqid('appraiser'),
			'password' => 'password',
			'w9' => [
				'id' => $capture->get('createW9.id'),
				'token' => $capture->get('createW9.token')
			],
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'dummy',
					'state' => 'TX'
				],
			],
			'eo' => [
				'document' => [
					'id' => $capture->get('createEoDocument.id'),
					'token' => $capture->get('createEoDocument.token')
				]
			]
		]);

		$data['qualifications']['primaryLicense']['certifications'] = [Certification::CERTIFIED_GENERAL];

        $data['firstName'] = 'Zack';
        $data['lastName'] = 'Weekend';

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'body' => $data
			]
		];
	},

	'getAppraiserWithLicensesState' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers',
				'parameters' => [
					'filter' => [
						'licenses' => [
							'state' => 'NE'
						]
					]
				],
			],
			'response' => [
				'total' => 1,
				'body' => [
					'id' => $capture->get('createAppraiser.id')
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function(){
						return true;
					}),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},
	'getAppraiserWithLicensesCounty' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers',
				'parameters' => [
					'filter' => [
						'licenses' => [
							'coverage' => [
								'county' => $runtime->getHelper()->county('CHASE', 'NE')
							]
						]
					]
				],
			],
			'response' => [
				'total' => 1,
				'body' => [
					'id' => $capture->get('createAppraiser.id')
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function(){
						return true;
					}),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},
	'getAppraiserWithLicensesZips' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers',
				'parameters' => [
					'filter' => [
						'licenses' => [
							'coverage' => [
								'zips' => '69045'
							]
						]
					]
				],
			],
			'response' => [
				'total' => 1,
				'body' => [
					'id' => $capture->get('createAppraiser.id')
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function(){
						return true;
					}),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},

	'getAppraiserWithLicensesCertifications' => function(){

        return [
            'request' => [
                'url' => 'GET /appraisers',
                'parameters' => [
                    'filter' => [
                        'licenses' => [
                            'certifications' => implode(',', [Certification::LICENSED, Certification::CERTIFIED_GENERAL])
                        ]
                    ]
                ],
                'includes' => ['qualifications']
            ],
            'response' => [
                'assert' => function(Response $response){
                    $body = $response->getBody();

                    if (count($body) < 2){
                        return false;
                    }

                    foreach ($body as $row){
                        $certifications = $row['qualifications']['primaryLicense']['certifications'];

                        if (!in_array(Certification::LICENSED, $certifications)
                            && !in_array(Certification::CERTIFIED_GENERAL, $certifications)){
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ];
    },

    'getAppraiserWithFullName' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers',
                'parameters' => [
                    'search' => [
                        'fullName' => 'Zack Week'
                    ]
                ],
            ],
            'response' => [
                'total' => 1,
                'body' => [
                    'id' => $capture->get('createAppraiser2.id')
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function(){
                        return true;
                    }),
                    new ItemFieldsFilter(['id'], true)
                ])
            ]
        ];
    },
];