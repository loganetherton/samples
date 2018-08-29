<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Invitation\Enums\Requirement;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Invitation\Enums\Status;
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

	'createResume:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
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

	'createOrder' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['invitation']['requirements'] = [Requirement::RESUME];

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'includes' => ['invitation'],
				'body' => $data
			],
			'response' => [
				'body' => [
					'invitation' => [
						'id' => new Dynamic(Dynamic::INT),
						'status' => Status::PENDING,
						'requirements' => [Requirement::RESUME],
						'ascAppraiser' => null,
						'appraiser' => new Dynamic(function($v) use ($capture){
							return $v['id'] == $capture->get('createAppraiser.id');
						}),
						'customer' => new Dynamic(function($v) use ($session){
							return $v['id'] = $session->get('user.id');
						}),
						'createdAt' => new Dynamic(Dynamic::DATETIME),
						'reference' => new Dynamic(Dynamic::STRING)
					],
				],
				'filter' => new ItemFieldsFilter(['invitation'], true)
			]
		];
	},

	'createOrderWithSharedInvitation' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['invitation']['requirements'] = [Requirement::RESUME];

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'includes' => ['invitation'],
				'body' => $data
			],
			'response' => [
				'body' => [
					'invitation' => [
						'id' => $capture->get('createOrder.invitation.id'),
						'status' => Status::PENDING
					],
				],
				'filter' => new ItemFieldsFilter(['invitation.id', 'invitation.status'], true)
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

	'getInvitationPending' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('createOrder.invitation.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'status' => Status::PENDING,
					'requirements' => [Requirement::RESUME],
					'ascAppraiser' => null,
					'appraiser' => new Dynamic(function($v) use ($capture){
						return $v['id'] == $capture->get('createAppraiser.id');
					}),
					'customer' => new Dynamic(function($v) use ($session){
						return $v['id'] = $session->get('user.id');
					}),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'reference' => new Dynamic(Dynamic::STRING)
				]
			]
		];
	},

	'tryAcceptOrderAndInvitation' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		return [
			'request' => [
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id')
					.'/orders/'.$capture->get('createOrder.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},

	'updateAppraiser:init' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiser.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('loginAppraiser.token')
				],
				'body' => [
					'qualifications' => [
						'resume' => [
							'id' => $capture->get('createResume.id'),
							'token' => $capture->get('createResume.token')
						]
					]
				]
			],
		];
	},

	'acceptOrderAndInvitation' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		return [
			'request' => [
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id')
					.'/orders/'.$capture->get('createOrder.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('loginAppraiser.token')
				]
			]
		];
	},

	'getInvitationAccepted' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('createOrder.invitation.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'status' => Status::ACCEPTED
				],
				'filter' => new ItemFieldsFilter(['status'], true)
			]
		];
	},

	'getOrderWithAcceptedInvitation' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['invitation']
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'invitation' => null
				]
			]
		];
	}
];
