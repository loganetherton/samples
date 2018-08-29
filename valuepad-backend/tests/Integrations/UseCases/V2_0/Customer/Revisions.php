<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;


$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

return [
	'createOrder:init' => function(Runtime $runtime) use ($dueDate){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['jobType'] = 3;

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $data,
				'includes' => ['property', 'customer']
			]
		];
	},
	'accept:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/accept',
			]
		];
	},
	'scheduleInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/schedule-inspection',
				'body' => [
					'scheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			]
		];
	},
	'completeInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $completedAt){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/complete-inspection',
				'body' => [
					'completedAt' => $completedAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			]
		];
	},
	'createPdf:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'complete:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'primary' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			]
		];
	},
	'create1' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/revisions',
				'auth' => 'customer',
				'body' => [
					'checklist' => ['Item #1', 'Item #2', 'Item #3']
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'checklist' => ['Item #1', 'Item #2', 'Item #3'],
					'message' => null,
					'createdAt' => new Dynamic(Dynamic::DATETIME)
				]
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::REVISION_REQUEST;
						})
					],
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:update-process-status',
						'data' => [
							'order' => new Dynamic(function($data) use ($capture){
								return $data['id'] == $capture->get('createOrder.id');
							}),
							'oldProcessStatus' => ProcessStatus::READY_FOR_REVIEW,
							'newProcessStatus' => ProcessStatus::REVISION_PENDING
						]
					]
				]
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
								return starts_with($value, 'Revision Request - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			},

			'mobile' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');
				$capture = $runtime->getCapture();

				$order = $capture->get('createOrder');

				return [
					'body' => [
						[
							'users' => [$session->get('user.id')],
							'notification' => [
								'category' => 'order',
								'name' => 'revision-request'
							],
							'message' => sprintf(
								'You have received a revision request on %s, %s, %s %s from %s.',
								$order['property']['address1'],
								$order['property']['city'],
								$order['property']['state']['code'],
								$order['property']['zip'],
								$order['customer']['name']
							),
							'extra' => [
								'order' => $capture->get('createOrder.id')
							]
						]
					]
				];
			}
		];
	},
	'getRevisionPendingLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$order = $capture->get('createOrder');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::REVISION_REQUEST,
					'message' => sprintf(
						'You have received a revision request on %s, %s, %s %s from %s.',
						$order['property']['address1'],
						$order['property']['city'],
						$order['property']['state']['code'],
						$order['property']['zip'],
						$order['customer']['name']
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'customer' => $capture->get('createOrder.customer.name'),
						'address1' => $capture->get('createOrder.property.address1'),
						'address2' => $capture->get('createOrder.property.address2'),
						'city' => $capture->get('createOrder.property.city'),
						'zip' => $capture->get('createOrder.property.zip'),
						'state' => $capture->get('createOrder.property.state'),
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::REVISION_REQUEST;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},
	'getRevisedOrder' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id')
					.'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus', 'revisionReceivedAt']
			],
			'response' => [
				'body' => [
					'processStatus' => 'revision-pending',
					'revisionReceivedAt' => new Dynamic(Dynamic::DATETIME)
				],
				'filter' => new ItemFieldsFilter(['processStatus', 'revisionReceivedAt'], true)
			]
		];
	},
	'getAll1' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/revisions',
				'auth' => 'customer',
			],
			'response' => [
				'body' => [$capture->get('create1')]
			]
		];
	},
	'create2' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/revisions',
				'auth' => 'customer',
				'body' => [
					'message' => 'All Items'
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'checklist' => [],
					'message' => 'All Items',
					'createdAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},
	'getAll2' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/revisions',
				'auth' => 'customer',
			],
			'response' => [
				'body' => [$capture->get('create1'), $capture->get('create2')]
			]
		];
	},
];