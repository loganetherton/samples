<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;

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

        $data = array_merge($data, [
            'address1' => '123 Wall Str.',
            'address2' => '124B Wall Str.',
            'city' => 'New York',
            'zip' => '44211',
            'state' => 'NY',
        ]);

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'includes' => ['qualifications'],
				'body' => $data
			]
		];
	},

	'signinAppraiser:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $appraiser,
				'password' => 'password'
			]
		]
	],


	'getEmptyCreditCardNumber' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'number' => null,
                    'address' => null,
                    'city' => null,
                    'state' => null,
                    'zip' => null
				]
			]
		];
	},
	'required' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'expiresAt' => [
						'month' => (int) (new DateTime('+31 days'))->format('n'),
						'year' => (int) (new DateTime('+31 days'))->format('Y'),
					],
				]
			],
			'response' => [
				'errors' => [
					'number' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'code' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'validate1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'number' => '111111234',
					'expiresAt' => [
						'month' => (int) (new DateTime('-31 days'))->format('n'),
						'year' => (int) (new DateTime('-31 days'))->format('Y'),
					],
					'code' => '12351'
				]
			],
			'response' => [
				'errors' => [
					'number' => [
						'identifier' => 'length',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'expiresAt' => [
						'identifier' => 'expired',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'code' => [
						'identifier' => 'length',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'validate2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'number' => 'dfawt12gsrt42',
					'code' => 'adf'
				]
			],
			'response' => [
				'errors' => [
					'number' => [
						'identifier' => 'numeric',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'expiresAt' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'code' => [
						'identifier' => 'numeric',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},

	'createCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'number' => '1111111111111116',
					'expiresAt' => [
						'month' => 10,
						'year' => (int) (new DateTime('+5 years'))->format('Y')
					],
					'code' => '241'
				]
			],
			'response' => [
				'body' => [
					'number' => '1116',
                    'address' => '123 Wall Str.',
                    'city' => 'New York',
                    'zip' => '44211',
                    'state' => [
                        'code' => 'NY',
                        'name' => 'New York'
                    ],
				]
			]
		];
	},
	'getCreatedCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
			],
			'response' => [
				'body' => [
					'number' => '1116',
                    'address' => '123 Wall Str.',
                    'city' => 'New York',
                    'zip' => '44211',
                    'state' => [
                        'code' => 'NY',
                        'name' => 'New York'
                    ],
				]
			]
		];
	},
	'updateCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'number' => '1111111111111114',
					'expiresAt' => [
						'month' => 10,
						'year' => (int) (new DateTime('+5 years'))->format('Y')
					],
					'code' => '241',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => 'CA'
				]
			],
			'response' => [
				'body' => [
					'number' => '1114',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
				]
			]
		];
	},
	'getUpdatedCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
			],
			'response' => [
				'body' => [
					'number' => '1114',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
				]
			]
		];
	},
];
