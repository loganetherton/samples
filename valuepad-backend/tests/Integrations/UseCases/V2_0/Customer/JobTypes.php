<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

return [
	'validate' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer',
				'body' => [
					'title' => ' ',
					'local' => 99999
				]
			],
			'response' => [
				'errors' => [
					'title' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'local' => [
						'identifier' => 'exists',
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
				'url' => 'POST /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 1'
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'isCommercial' => false,
					'isPayable' => true,
					'title' => 'Test 1',
					'local' => null
				]
			]
		];
	},
	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer'
			],
			'response' => [
				'body' => $capture->get('create1'),
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create1.id');
				})
			]
		];
	},
	'update' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/job-types/'.$capture->get('create1.id'),
				'body' => [
					'title' => 'Test 1 [updated]',
					'local' => 1,
					'isCommercial' => true
				],
				'auth' => 'customer'
			]
		];
	},
	'getAllWithUpdated' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'title' => 'Test 1 [updated]',
					'isCommercial' => true,
					'local' => [
						'id' => 1
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create1.id');
					}),
					new ItemFieldsFilter(['title', 'local.id', 'isCommercial'], true)
				])
			]
		];
	},
	'tryCreateWithSameLocal' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 2 with same local',
					'local' => 1
				]
			],
			'response' => [
				'body' => [
					'title' => 'Test 2 with same local',
					'local' => [
						'id' => 1
					]
				],
				'filter' => new ItemFieldsFilter(['title', 'local.id'], true)
			]
		];
	},
	'unmap' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/job-types/'.$capture->get('create1.id'),
				'body' => [
					'local' => null
				],
				'auth' => 'customer'
			]
		];
	},
	'getAllUnmapped' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'title' => 'Test 1 [updated]',
					'local' => null
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create1.id');
					}),
					new ItemFieldsFilter(['title', 'local'], true)
				])
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/job-types/'.$capture->get('create1.id'),
				'auth' => 'customer'
			]
		];
	},
	'tryUpdateDeleted' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/job-types/'.$capture->get('create1.id'),
				'body' => [
					'title' => 'Will not be updated',
					'local' => 1
				],
				'auth' => 'customer'
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},

	'create2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 2'
				]
			]
		];
	},

	'createOrder:init' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['jobType'] = $capture->get('create2.id');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'deleteWithOrder' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/job-types/'.$capture->get('create2.id'),
				'auth' => 'customer'
			]
		];
	},
	'tryUpdateDeletedWithOrder' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/job-types/'.$capture->get('create2.id'),
				'body' => [
					'title' => 'Will not be updated',
				],
				'auth' => 'customer'
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},
	'getOrderWithDeletedJobType' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['jobType'] = $capture->get('create2.id');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id')
					.'/orders/'.$capture->get('createOrder.id'),
				'includes' => ['jobType'],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'jobType' => $capture->get('create2')
				]
			]
		];
	},

	/**
	 * It updates an order with already softly deleted job type.
	 * However, the deleted job type was assigned to this order prior deletion.
	 * Therefore, it should not throw an error saying the that job type does not exists.
	 */
	'updateOrderWithDeletedJobType' => function(Runtime $runtime) {
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['jobType'] = $capture->get('create2.id');

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id')
					.'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'body' => [
					'jobType' => $capture->get('create2.id')
				]
			]
		];
	},

	'create3:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/job-types',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test 3'
				]
			]
		];
	},

	'assignFees:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$appraiserSession->get('user.id').'/customers/'
					.$customerSession->get('user.id').'/fees',
				'body' => [
					'jobType' => $capture->get('create3.id'),
					'amount' => 100
				]
			]
		];
	},

	'getAllWithAssignedFees' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/fees',
				'auth' => 'customer',
			],
			'response' => [
				'assert' => function(Response $response) use ($capture){
					$data = $response->getBody();

					foreach ($data as $item){
						if ($item['id'] == $capture->get('assignFees.id')){
							return true;
						}
					}

					return false;
				}
			]
		];
	},

	'deleteWithFees:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/job-types/'.$capture->get('create3.id'),
				'auth' => 'customer'
			]
		];
	},

	'getAllWithDeletedFees' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/fees',
				'auth' => 'customer',
			],
			'response' => [
				'assert' => function(Response $response) use ($capture){
					$data = $response->getBody();

					foreach ($data as $item){
						if ($item['id'] == $capture->get('assignFees.id')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},
];
