<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
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

	'createAdditionalStatusFromCustomer1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/settings/additional-statuses',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer1.token')
				],
				'body' => [
					'title' => 'Customer 1 Additional Status'
				],
			]
		];

	},


	//-------------

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

	'createAdditionalStatusFromCustomer2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/settings/additional-statuses',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer2.token')
				],
				'body' => [
					'title' => 'Customer 2 Additional Status'
				],
			]
		];

	},

	'getAllFromCustomer1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrderFromCustomer1.id').'/additional-statuses',
			],
			'response' => [
				'body' => [$capture->get('createAdditionalStatusFromCustomer1')]
			]
		];
	},

	'getAllFromCustomer2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrderFromCustomer2.id').'/additional-statuses',
			],
			'response' => [
				'body' => [$capture->get('createAdditionalStatusFromCustomer2')]
			]
		];
	},

	'tryChangeAdditionalStatusOfOrder2WithAdditionalStatusOfOrder1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderFromCustomer2.id').'/change-additional-status',
				'body' => [
					'additionalStatus' => $capture->get('createAdditionalStatusFromCustomer1.id')
				]
			],
			'response' => [
				'errors' => [
					'additionalStatus' => [
						'identifier' => 'permissions',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'changeAdditionalStatusOfOrder2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderFromCustomer2.id').'/change-additional-status',
				'body' => [
					'additionalStatus' => $capture->get('createAdditionalStatusFromCustomer2.id')
				]
			],
			'emails' => [
				'body' => []
			]
		];
	},

	'getOrderFromCustomer2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrderFromCustomer2.id'),
				'includes' => ['additionalStatus', 'additionalStatusComment'],
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderFromCustomer2.id'),
					'fileNumber' => $capture->get('createOrderFromCustomer2.fileNumber'),
					'additionalStatus' => $capture->get('createAdditionalStatusFromCustomer2'),
					'additionalStatusComment' => null
				]
			]
		];

	},

	'getOrderFromCustomer1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrderFromCustomer1.id'),
				'includes' => ['additionalStatus', 'additionalStatusComment'],
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderFromCustomer1.id'),
					'fileNumber' => $capture->get('createOrderFromCustomer1.fileNumber'),
					'additionalStatus' => null,
					'additionalStatusComment' => null
				]
			]
		];

	},

	'deleteAdditionalStatusFromCustomer2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$capture->get('createCustomer2.id')
					.'/settings/additional-statuses/'.$capture->get('createAdditionalStatusFromCustomer2.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer2.token')
				]
			]
		];
	},

	'getAllFromCustomer2AfterDelete' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderFromCustomer2.id').'/additional-statuses',
			],
			'response' => [
				'body' => []
			]
		];
	},

	'getOrderFromCustomer2Again' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrderFromCustomer2.id'),
				'includes' => ['additionalStatus', 'additionalStatusComment'],
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderFromCustomer2.id'),
					'fileNumber' => $capture->get('createOrderFromCustomer2.fileNumber'),
					'additionalStatus' => $capture->get('createAdditionalStatusFromCustomer2'),
					'additionalStatusComment' => null
				]
			]
		];

	},

	'tryChangeTheSameAdditionalStatusOfOrder2AfterDelete' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderFromCustomer2.id').'/change-additional-status',
				'body' => [
					'additionalStatus' => $capture->get('createAdditionalStatusFromCustomer2.id')
				]
			],
			'response' => [
				'errors' => [
					'additionalStatus' => [
						'identifier' => 'permissions',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},


	'createOrderForNotification:init' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');


		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'createAdditionalStatus1FromSessionCustomer:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
				'body' => [
					'title' => 'Appraiser Test #1',
					'comment' => 'Appraiser Comment Test #1'
				],
			]
		];

	},

	'createAdditionalStatus2FromSessionCustomer:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
				'body' => [
					'title' => 'Appraiser Test #2',
					'comment' => 'Appraiser Comment Test #2'
				],
			]
		];
	},

	'changeAdditionalStatusForOrderNotification1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForNotification.id').'/change-additional-status',
				'body' => [
					'additionalStatus' => $capture->get('createAdditionalStatus1FromSessionCustomer.id'),
					'comment' => 'To #1'
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'change-additional-status',
						'order' => $capture->get('createOrderForNotification.id'),
						'oldAdditionalStatus' => null,
						'oldAdditionalStatusComment' => null,
						'newAdditionalStatus' => $capture->get('createAdditionalStatus1FromSessionCustomer.id'),
						'newAdditionalStatusComment' => 'To #1'
					]
				]
			],
			'live' => [
				'body' => [
					'event' => 'order:change-additional-status',
                    'channels' => [
                        'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                    ],
					'data' => [
						'order' => [
							'id' => $capture->get('createOrderForNotification.id'),
							'fileNumber' => $capture->get('createOrderForNotification.fileNumber')
						],
						'oldAdditionalStatus' => null,
						'oldAdditionalStatusComment' => null,
						'newAdditionalStatus' => $capture->get('createAdditionalStatus1FromSessionCustomer'),
						'newAdditionalStatusComment' => 'To #1'
					]
				],
				'filter' => new FirstFilter(function($k, $v){
					return $v['event'] === 'order:change-additional-status';
				})
			],

			'mobile' => [
				'body' => []
			]
		];
	},

	'changeAdditionalStatusForOrderNotification2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForNotification.id').'/change-additional-status',
				'body' => [
					'additionalStatus' => $capture->get('createAdditionalStatus2FromSessionCustomer.id'),
					'comment' => 'To #2'
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'change-additional-status',
						'order' => $capture->get('createOrderForNotification.id'),
						'oldAdditionalStatus' => $capture->get('createAdditionalStatus1FromSessionCustomer.id'),
						'oldAdditionalStatusComment' => 'To #1',
						'newAdditionalStatus' => $capture->get('createAdditionalStatus2FromSessionCustomer.id'),
						'newAdditionalStatusComment' => 'To #2'
					]
				]
			],
			'live' => [
				'body' => [
					'event' => 'order:change-additional-status',
                    'channels' => [
                        'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                    ],
					'data' => [
						'order' => [
							'id' => $capture->get('createOrderForNotification.id'),
							'fileNumber' => $capture->get('createOrderForNotification.fileNumber')
						],
						'oldAdditionalStatus' => $capture->get('createAdditionalStatus1FromSessionCustomer'),
						'oldAdditionalStatusComment' => 'To #1',
						'newAdditionalStatus' => $capture->get('createAdditionalStatus2FromSessionCustomer'),
						'newAdditionalStatusComment' => 'To #2'
					]
				],
				'filter' => new FirstFilter(function($k, $v){
					return $v['event'] === 'order:change-additional-status';
				})
			]
		];
	},
];