<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;

$customer = uniqid('customer');

return [
	'createCustomer:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => $customer,
				'password' => 'password',
				'name' => $customer
			]
		]
	],
	'signinCustomer:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $customer,
				'password' => 'password'
			]
		]
	],
	'invite:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/invitations',
				'body' => [
					'ascAppraiser' => 4
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				]
			]
		];

	},
	'accept:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/invitations/'
					.$capture->get('invite.id').'/accept',
			]
		];
	},
	'addJobType1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test 1'
				]
			]
		];
	},
	'addFormats1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/settings/documents/formats',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'jobType' => $capture->get('addJobType1.id'),
					'primary' => ['pdf'],
					'extra' => ['aci']
				]
			],
		];
	},
	'addJobType2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test 2'
				]
			]
		];
	},
	'addFormats2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/settings/documents/formats',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'jobType' => $capture->get('addJobType2.id'),
					'primary' => ['xml']
				]
			]
		];
	},
	'addJobType3:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test 3'
				]
			]
		];
	},

	'addClient:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/clients',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'name' => 'Wonderful World'
				]
			]
		];
	},

	'createOrder1:init' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClient.id'),
			'clientDisplayedOnReport' => $capture->get('addClient.id')
		]);

		$data['jobType'] = $capture->get('addJobType1.id');


		return [
			'request' => [
				'url' => 'POST /customers/'
					.$capture->get('createCustomer.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => $data
			]
		];
	},
	'createOrder2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClient.id'),
			'clientDisplayedOnReport' => $capture->get('addClient.id')
		]);

		$data['jobType'] = $capture->get('addJobType2.id');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$capture->get('createCustomer.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => $data
			]
		];
	},
	'createOrder3:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClient.id'),
			'clientDisplayedOnReport' => $capture->get('addClient.id')
		]);

		$data['jobType'] = $capture->get('addJobType3.id');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$capture->get('createCustomer.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => $data
			]
		];
	},
	'formats1' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/document/formats',
			],
			'response' => [
				'body' => [
					'primary' => ['pdf'],
					'extra' => ['aci']
				]
			]
		];
	},
	'formats2' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$capture->get('createOrder2.id').'/document/formats',
			],
			'response' => [
				'body' => [
					'primary' => ['xml'],
					'extra' => null
				]
			]
		];
	},
	'formats3' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$capture->get('createOrder3.id').'/document/formats',
			],
			'response' => [
				'body' => [
					'primary' => new Dynamic(function($v){
						return in_array('xml', $v) && in_array('pdf', $v);
					}),
					'extra' => []
				]
			]
		];
	}
];