<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
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

	'signinAppraiser:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $appraiser,
				'password' => 'password'
			]
		]
	],

	'acceptInvitation:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
		];
	},


	'createOrder1:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['isPaid'] = true;
		$data['fee'] = 100.04;
		$data['techFee'] = 55.12;
		$data['paidAt'] = (new DateTime('-10 days'))->format(DateTime::ATOM);


		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['orderedAt'] = (new DateTime('-6 days'))->format(DateTime::ATOM);
		$data['isPaid'] = true;
		$data['fee'] = 34.55;
		$data['techFee'] = 11.98;
		$data['paidAt'] = (new DateTime('-10 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder3:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['isPaid'] = false;
		$data['fee'] = 66.09;
		$data['techFee'] = 11.92;

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder4:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['orderedAt'] = (new DateTime('-7 days'))->format(DateTime::ATOM);
		$data['isPaid'] = false;
		$data['fee'] = 921.09;
		$data['techFee'] = 552.5;

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'getTotals' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/orders/totals',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'paid' => [
						'total' => 2,
						'fee' => 134.59,
						'techFee' => 67.1
					],
					'unpaid' => [
						'total' => 2,
						'fee' => 987.18,
						'techFee' => 564.42
					],
				]
			]
		];
	},

	'getTotalsWithFilter' => function (Runtime $runtime) {
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/orders/totals',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'parameters' => [
					'filter' => [
						'orderedAt' => [
							'from' => (new DateTime('-8 days'))->format('Y-m-d'),
							'to' => (new DateTime('-5 days'))->format('Y-m-d'),
						]
					]
				]
			],
			'response' => [
				'body' => [
					'paid' => [
						'total' => 1,
						'fee' => 34.55,
						'techFee' => 11.98,
					],
					'unpaid' => [
						'total' => 1,
						'fee' => 921.09,
						'techFee' => 552.5,
					]
				]
			]
		];
	}
];