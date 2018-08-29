<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;

$customer = uniqid('customer');

return [
	'validate' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
				'body' => [
					'jobType' => 10000,
					'amount' => -10.99
				]
			],
			'response' => [
				'errors' => [
					'jobType' => [
						'identifier' => 'access',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'amount' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'create1' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
				'body' => [
					'jobType' => 10,
					'amount' => 10.99
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'jobType' => [
						'id' => 10,
						'isCommercial' => false,
						'isPayable' => true,
						'title' => new Dynamic(Dynamic::STRING),
						'local' => new Dynamic(function($v){
							return is_array($v);
						})
					],
					'amount' => 10.99
				]
			]
		];
	},
	'tryCreate2' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
				'body' => [
					'jobType' => 10,
					'amount' => 10.99
				]
			],
			'response' => [
				'errors' => [
					'jobType' => [
						'identifier' => 'already-taken',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'create2' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
				'body' => [
					'jobType' => 13,
					'amount' => 0.99
				]
			]
		];
	},

	'getAll' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'GET /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
			],
			'response' => [
				'total' => 2
			]
		];
	},
	'tryUpdate' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'PATCH /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees/'
					.$capture->get('create2.id'),
				'body' => [
					'jobType' => 14,
					'amount' => 0.99
				]
			],
			'response' => [
				'errors' => [
					'jobType' => [
						'identifier' => 'read-only',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'update' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'PATCH /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees/'
					.$capture->get('create2.id'),
				'body' => [
					'amount' => 40.45
				]
			]
		];
	},
	'getAllAfterUpdating' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
			],
			'response' => [
				'body' => [
					'amount' => 40.45
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create2.id');
					}),
					new ItemFieldsFilter(['amount'], true)
				])
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'DELETE /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees/'
					.$capture->get('create1.id'),
			]
		];
	},
	'getAllAfterDeleting' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
			],
			'response' => [
				'total' => 1,
				'assert' => function(Response $response) use ($capture){
					return $response->getBody()[0]['id'] == $capture->get('create2.id');
				}
			]
		];
	},

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

	'addJobTypeFromCustomer:init' => function(Runtime $runtime){
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

	'create3' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/customers/'
					.$capture->get('createCustomer.id').'/fees',
				'body' => [
					'jobType' => $capture->get('addJobTypeFromCustomer.id'),
					'amount' => 0.99
				]
			]
		];
	},
];