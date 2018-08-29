<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use Ascope\QA\Support\Response;
use Ascope\QA\Integrations\Checkers\Dynamic;

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

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'includes' => ['qualifications'],
				'body' => $data
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
						'licenseNumber' => $capture->get('createAppraiser.qualifications.primaryLicense.number')
					],
					'filter' => [
						'licenseState' => $capture->get('createAppraiser.qualifications.primaryLicense.state.code')
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
				'username' => $appraiser,
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

	'tryGet' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/appraisers/'.$capture->get('createAppraiser.id').'/ach',
				'auth' => 'customer',
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},

	'acceptInvitation:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
		];
	},

	'replace:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$capture->get('createAppraiser.id').'/ach',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'body' => [
					'bankName' => 'Bank of America',
					'accountNumber' => '12345678901234567890',
					'accountType' => AchAccountType::CHECKING,
					'routing' => '123456789'
				]
			]
		];
	},

	'get' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/appraisers/'.$capture->get('createAppraiser.id').'/ach',
				'auth' => 'customer',
			],
			'response' => [
				'body' => [
					'bankName' => 'Bank of America',
					'accountNumber' => '7890',
					'accountType' => AchAccountType::CHECKING,
					'routing' => '6789',
                    'encryptedAccountNumber' => new Dynamic(Dynamic::STRING),
                    'encryptedRouting' => new Dynamic(Dynamic::STRING)
				]
			]
		];
	}
];