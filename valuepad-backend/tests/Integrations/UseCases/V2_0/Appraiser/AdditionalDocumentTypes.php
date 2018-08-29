<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Support\Filters\FirstFilter;

$customer1 = uniqid('customer');
$customer2 = uniqid('customer');

return [
	'createCustomer1:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => $customer1,
				'password' => 'password',
				'name' => $customer1
			]
		]
	],
	'signinCustomer1:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $customer1,
				'password' => 'password'
			]
		]
	],
	'invite1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/invitations',
				'body' => [
					'ascAppraiser' => 4
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer1.token')
				]
			]
		];

	},
	'accept1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/invitations/'
					.$capture->get('invite1.id').'/accept',
			]
		];
	},
	'addJobTypeFromCustomer1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer1.token')
				],
				'body' => [
					'title' => 'Test 1'
				]
			]
		];
	},
	'addClientFromCustomer1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/clients',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer1.token')
				],
				'body' => [
					'name' => 'Wonderful World'
				]
			]
		];
	},
	'createOrderFromCustomer1:init' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClientFromCustomer1.id'),
			'clientDisplayedOnReport' => $capture->get('addClientFromCustomer1.id')
		]);

		$data['jobType'] = $capture->get('addJobTypeFromCustomer1.id');


		return [
			'request' => [
				'url' => 'POST /customers/'
					.$capture->get('createCustomer1.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer1.token')
				],
				'body' => $data
			]
		];
	},

	//---------------------------

	'createCustomer2:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => $customer2,
				'password' => 'password',
				'name' => $customer2
			]
		]
	],
	'signinCustomer2:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $customer2,
				'password' => 'password'
			]
		]
	],
	'invite2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/invitations',
				'body' => [
					'ascAppraiser' => 4
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer2.token')
				]
			]
		];

	},
	'accept2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/invitations/'
					.$capture->get('invite2.id').'/accept',
			]
		];
	},
	'addJobTypeFromCustomer2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer2.token')
				],
				'body' => [
					'title' => 'Test 1'
				]
			]
		];
	},
	'addClientFromCustomer2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/clients',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer2.token')
				],
				'body' => [
					'name' => 'Wonderful World'
				]
			]
		];
	},
	'createOrderFromCustomer2:init' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClientFromCustomer2.id'),
			'clientDisplayedOnReport' => $capture->get('addClientFromCustomer2.id')
		]);

		$data['jobType'] = $capture->get('addJobTypeFromCustomer2.id');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$capture->get('createCustomer2.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer2.token')
				],
				'body' => $data
			]
		];
	},

	'addAdditionalDocumentType:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer2.id')
					.'/settings/additional-documents/types',
				'headers' => [
					'Token' => $capture->get('signinCustomer2.token')
				],
				'auth' => 'guest',
				'body' => [
					'title' => 'Test type'
				]
			]
		];
	},

	'additionalDocumentType1' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderFromCustomer1.id').'/additional-documents/types',
			],
			'response' => [
				'body' => []
			]
		];
	},

	'additionalDocumentType2' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderFromCustomer2.id').'/additional-documents/types',
			],
			'response' => [
				'body' => [
					'id' => $capture->get('addAdditionalDocumentType.id'),
					'title' => 'Test type'
				],
				'filter' => new FirstFilter(function(){
					return true;
				})
			]
		];
	}
];