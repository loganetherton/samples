<?php
use ValuePad\Core\Customer\Enums\CompanyType;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

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

	'invite:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => $capture->get('getAscAppraiser.0.id')
				],
				'auth' => 'customer',
			]
		];
	},

	'loginAppraiser:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $appraiser,
				'password' => 'password'
			]
		]
	],

	'getAllEmpty' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/settings',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'notifications' => []
				]
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
	'createOrder:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				]),
				'includes' => ['property']
			]
		];
	},

	'getAll1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/settings',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'notifications' => [
						[
							'customer' => [
								'id' => new Dynamic(Dynamic::INT),
								'username' => new Dynamic(Dynamic::STRING),
								'name' => new Dynamic(Dynamic::STRING),
								'displayName' => new Dynamic(Dynamic::STRING),
								'companyType' => CompanyType::APPRAISAL_MANAGEMENT_COMPANY,
                                'type' => 'customer'
							],
							'email' => true
						]
					],
				]
			]
		];
	},

	'updateOrderGetEmail' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'body' => [
					'referenceNumber' => 'YYYXXXZZZ'
				]
			],
			'emails' => [
				'body' => [
					[
						'from' => [
							'no-reply@valuepad.com' => 'The ValuePad Team'
						],
						'to' => [
							$capture->get('createAppraiser.email') => $capture->get('createAppraiser.displayName'),
						],
						'subject' => new Dynamic(function($value) use ($capture){
							return starts_with($value, 'Updated - Order on '.$capture->get('createOrder.property.address1'));
						}),
						'contents' => new Dynamic(Dynamic::STRING)
					]
				]
			]
		];
	},

	'update' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiser.id').'/settings',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				],
				'body' => [
					'notifications' => [
						[
							'customer' => $session->get('user.id'),
							'email' => false
						]
					],
				]
			]
		];
	},

	'updateOrderNotGetEmail' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'body' => [
					'referenceNumber' => 'AAABBBCCC'
				]
			],
			'emails' => [
				'body' => []
			]
		];
	},

	'getAll2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/settings',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'notifications' => [
						[
							'customer' => [
								'id' => new Dynamic(Dynamic::INT),
								'username' => new Dynamic(Dynamic::STRING),
								'name' => new Dynamic(Dynamic::STRING),
								'displayName' => new Dynamic(Dynamic::STRING),
                                'type' => 'customer',
								'companyType' => CompanyType::APPRAISAL_MANAGEMENT_COMPANY
							],
							'email' => false
						]
					],
				]
			]
		];
	},

];