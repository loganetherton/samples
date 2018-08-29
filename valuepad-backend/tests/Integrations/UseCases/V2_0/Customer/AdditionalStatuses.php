<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;

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

	'createOrder:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'includes' => ['property'],
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'validateRequired' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Comment 1'
				],
			],
			'response' => [
				'errors' => [
					'title' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'create1' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 1',
					'comment' => 'Comment 1'
				],
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'title' => 'Test 1',
					'comment' => 'Comment 1'
				]
			]
		];

	},

	'createForeign' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/settings/additional-statuses',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test 2'
				],
			]
		];

	},

	'validateChangeAdditionalStatus' => function(Runtime $runtime){

		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/change-additional-status',
				'auth' => 'customer',
				'body' => [
					'comment' => 'test'
				]
			],
			'response' => [
				'errors' => [
					'additionalStatus' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'changeAdditionalStatus1' => function(Runtime $runtime){

		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/change-additional-status',
				'auth' => 'customer',
				'body' => [
					'additionalStatus' => $capture->get('create1.id'),
					'comment' => 'The additional status 1'
				]
			],
			'push' => [
				'body' => []
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
							'id' => $capture->get('createOrder.id'),
							'fileNumber' => $capture->get('createOrder.fileNumber')
						],
						'oldAdditionalStatus' => null,
						'oldAdditionalStatusComment' => null,
						'newAdditionalStatus' => $capture->get('create1'),
						'newAdditionalStatusComment' => 'The additional status 1'
					]
				],
				'filter' => new FirstFilter(function($k, $v){
					return $v['event'] === 'order:change-additional-status';
				})
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, $capture->get('create1.title').' - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(function($value) use ($capture){
								return is_string($value) && $value !== null
									&& str_contains($value, 'Current Additional Status: '.$capture->get('create1.title')
									.' - The additional status 1');
							})
						]
					]
				];
			},

			'mobile' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');
				$capture = $runtime->getCapture();

				return [
					'body' => [
						[
							'users' => [$session->get('user.id')],
							'notification' => [
								'category' => 'order',
								'name' => 'change-additional-status'
							],
							'message' => new Dynamic(function($value) use ($capture){
								return str_contains($value, $capture->get('create1.title'));
							}),
							'extra' => [
								'order' => $capture->get('createOrder.id')
							]
						]
					]
				];
			}
		];
	},

	'getAdditionalStatusLogs1' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::CHANGE_ADDITIONAL_STATUS,
					'message' => sprintf(
						'%s has changed the additional status to "%s".',
						$customerSession->get('user.name'),
						$capture->get('create1.title')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldAdditionalStatus' => null,
						'oldAdditionalStatusComment' => null,
						'newAdditionalStatus' => [
							'title' => $capture->get('create1.title'),
							'comment' => $capture->get('create1.comment')
						],
						'newAdditionalStatusComment' => 'The additional status 1'
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::CHANGE_ADDITIONAL_STATUS;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},


	'getOrderWithAdditionalStatus' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['additionalStatus', 'additionalStatusComment']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'additionalStatus' => $capture->get('create1'),
					'additionalStatusComment' => 'The additional status 1'
				]
			]
		];
	},


	'tryCreateWithTheSameTitle' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 1'
				],
			],
			'response' => [
				'errors' => [
					'title' => [
						'identifier' => 'unique',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];

	},

	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
			],
			'response' => [
				'body' => $capture->get('create1'),
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create1.id');
				})
			]
		];
	},


	'updateTitleTheSame' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/settings/additional-statuses/'.$capture->get('create1.id'),
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 1',
				]
			]
		];

	},

	'update' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/settings/additional-statuses/'.$capture->get('create1.id'),
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 1 [Updated]',
					'comment' => 'Comment 1 [Updated]'
				]
			]
		];

	},
	'getAllUpdated' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$data = $capture->get('create1');
		$data['title'] = 'Test 1 [Updated]';
		$data['comment'] = 'Comment 1 [Updated]';

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
			],
			'response' => [
				'body' => $data,
				'filter' => new FirstFilter(function($k, $v) use ($data){
					return $v['id'] == $data['id'];
				})
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/settings/additional-statuses/'.$capture->get('create1.id'),
				'auth' => 'customer',
			]
		];
	},

	'getAllDeleted' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
			],
			'response' => [
				'body' => [],
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create1.id');
				})
			]
		];
	},

	'tryChangeAdditionalStatusWithDeleted' => function(Runtime $runtime){

		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/change-additional-status',
				'auth' => 'customer',
				'body' => [
					'additionalStatus' => $capture->get('create1.id')
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

	'tryChangeAdditionalStatusWithForeign' => function(Runtime $runtime){

		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/change-additional-status',
				'auth' => 'customer',
				'body' => [
					'additionalStatus' => $capture->get('createForeign.id')
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


	'create2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/additional-statuses',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 2',
					'comment' => 'Comment 2'
				],
			]
		];
	},

	'changeAdditionalStatus2' => function(Runtime $runtime){

		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$additionalStatus1 = $capture->get('create1');
		$additionalStatus1['title'] = 'Test 1 [Updated]';
		$additionalStatus1['comment'] = 'Comment 1 [Updated]';

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/change-additional-status',
				'auth' => 'customer',
				'body' => [
					'additionalStatus' => $capture->get('create2.id'),
					'comment' => 'The additional status 2'
				]
			],
			'push' => [
				'body' => []
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
							'id' => $capture->get('createOrder.id'),
							'fileNumber' => $capture->get('createOrder.fileNumber')
						],
						'oldAdditionalStatus' => $additionalStatus1,
						'oldAdditionalStatusComment' => 'The additional status 1',
						'newAdditionalStatus' => $capture->get('create2'),
						'newAdditionalStatusComment' => 'The additional status 2'
					]
				],
				'filter' => new FirstFilter(function($k, $v){
					return $v['event'] === 'order:change-additional-status';
				})
			]
		];
	},

	'getAdditionalStatusLogs2' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::CHANGE_ADDITIONAL_STATUS,
					'message' => sprintf(
						'%s has changed the additional status to "%s".',
						$customerSession->get('user.name'),
						$capture->get('create2.title')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldAdditionalStatus' => [
							'title' => 'Test 1 [Updated]',
							'comment' => 'Comment 1 [Updated]'
						],
						'oldAdditionalStatusComment' => 'The additional status 1',
						'newAdditionalStatus' => [
							'title' => $capture->get('create2.title'),
							'comment' => $capture->get('create2.comment')
						],
						'newAdditionalStatusComment' => 'The additional status 2'
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::CHANGE_ADDITIONAL_STATUS
						&& $v['extra']['oldAdditionalStatus'] !== null;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},
];
