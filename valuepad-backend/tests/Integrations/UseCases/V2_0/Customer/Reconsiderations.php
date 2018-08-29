<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Core\Log\Enums\Action;

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
	'createPdf1:init' => [
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

	'validate' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment',
                    'document' => [
                        'type' => null
                    ]
				]
			],
			'response' => [
				'errors' => [
                    'document.label' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'document.document' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
				]
			]
		];
	},

	'validateDocuments' => function (Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'documents' => [
						[
							'type' => null,
						]
					]
				]
			],
			'response' => [
				'errors' => [
					'documents' => [
						'identifier' => 'collection',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => [
							0 => [
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'label' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'document' => [
										'identifier' => 'required',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							]
						]
					]
				]
			]
		];
	},

	'validateComparable' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$closedDate1 = (new DateTime('+1 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment',
					'comparables' => [
						[
							'address' => 'Address 1',
							'salesPrice' => 1.1,
							'closedDate' => $closedDate1,
							'livingArea' => 'Some area to live 1',
							'siteSize' => 'Large 1',
							'actualAge' => 'old 1',
							'distanceToSubject' => 'Long 1',
							'sourceData' => 'Some source 1',
							'comment' => 'Some comment 1'
						],
						[
							'address' => '  ',
							'salesPrice' => -1,
							'livingArea' => '  ',
							'siteSize' => '  ',
							'actualAge' => '  ',
							'distanceToSubject' => '  ',
							'sourceData' => '  ',
							'comment' => '  '
						]
					]
				]
			],
			'response' => [
				'errors' => [
					'comparables' => [
						'identifier' => 'collection',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => [
							1 => [
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'address' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'salesPrice' => [
										'identifier' => 'greater',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'livingArea' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'siteSize' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'actualAge' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'distanceToSubject' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									],
									'sourceData' => [
										'identifier' => 'empty',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							]
						]
					]
				]
			]
		];
	},

	'create1' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$closedDate1 = (new DateTime('+1 days'))->format(DateTime::ATOM);
		$closedDate2 = (new DateTime('+2 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment',
                    'document' => [
                        'document' => [
                            'id' => $runtime->getCapture()->get('createPdf.id'),
                            'token' => $runtime->getCapture()->get('createPdf.token')
                        ],
                        'label' => 'Document #1'
                    ],
					'comparables' => [
						[
							'address' => 'Address 1',
							'salesPrice' => 1.1,
							'closedDate' => $closedDate1,
							'livingArea' => 'Some area to live 1',
							'siteSize' => 'Large 1',
							'actualAge' => 'old 1',
							'distanceToSubject' => 'Long 1',
							'sourceData' => 'Some source 1',
							'comment' => 'Some comment 1'
						],
						[
							'address' => 'Address 2',
							'salesPrice' => 2.2,
							'closedDate' => $closedDate2,
							'livingArea' => 'Some area to live 2',
							'siteSize' => 'Large 2',
							'actualAge' => 'old 2',
							'distanceToSubject' => 'Long 2',
							'sourceData' => 'Some source 2',
							'comment' => 'Some comment 2'
						]
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'comment' => 'Test comment',
                    'document' => [
                        'id' => new Dynamic(Dynamic::INT),
                        'type' => null,
                        'label' => 'Document #1',
                        'document' => $runtime->getCapture()->get('createPdf'),
                        'createdAt' => new Dynamic(Dynamic::DATETIME)
                    ],
					'comparables' => [
						[
							'address' => 'Address 1',
							'salesPrice' => 1.1,
							'closedDate' => $closedDate1,
							'livingArea' => 'Some area to live 1',
							'siteSize' => 'Large 1',
							'actualAge' => 'old 1',
							'distanceToSubject' => 'Long 1',
							'sourceData' => 'Some source 1',
							'comment' => 'Some comment 1'
						],
						[
							'address' => 'Address 2',
							'salesPrice' => 2.2,
							'closedDate' => $closedDate2,
							'livingArea' => 'Some area to live 2',
							'siteSize' => 'Large 2',
							'actualAge' => 'old 2',
							'distanceToSubject' => 'Long 2',
							'sourceData' => 'Some source 2',
							'comment' => 'Some comment 2'
						]
					],
					'documents' => [],
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
							return $data['action'] === Action::RECONSIDERATION_REQUEST;
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
								return starts_with($value, 'Reconsideration Request - Order on '.$capture->get('createOrder.property.address1'));
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
								'name' => 'reconsideration-request'
							],
							'message' => sprintf(
								'You have received a reconsideration request on %s, %s, %s %s from %s.',
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
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$order['id'].'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::RECONSIDERATION_REQUEST,
					'message' => sprintf(
						'You have received a reconsideration request on %s, %s, %s %s from %s.',
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
						return $v['action'] === Action::RECONSIDERATION_REQUEST;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getAll' => function(Runtime $runtime){

		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [$capture->get('create1')]
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

	'create2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$closedDate1 = (new DateTime('+1 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment',
					'comparables' => [
						[
							'address' => 'Address 3',
							'salesPrice' => 0,
							'closedDate' => $closedDate1,
							'livingArea' => 'Some area to live 3',
							'siteSize' => 'Large 3',
							'actualAge' => 'old 3',
							'distanceToSubject' => 'Long 3',
							'sourceData' => 'Some source 3',
							'comment' => 'Some comment 3'
						]
					]
				]
			]
		];
	},

	'create3WithMinimum' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment',
					'comparables' => [
						[
							'address' => 'Address 3'
						]
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'comment' => 'Test comment',
                    'document' => null,
					'comparables' => [
						[
							'address' => 'Address 3',
							'salesPrice' => null,
							'closedDate' => null,
							'livingArea' => null,
							'siteSize' => null,
							'actualAge' => null,
							'distanceToSubject' => null,
							'sourceData' => null,
							'comment' => null
						]
					],
					'documents' => [],
					'createdAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},

	'create4WithDocuments' => function (Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment',
					'comparables' => [
						[
							'address' => 'Address 3'
						]
					],
					'documents' => [
						[
	                        'document' => [
	                            'id' => $runtime->getCapture()->get('createPdf.id'),
	                            'token' => $runtime->getCapture()->get('createPdf.token')
	                        ],
	                        'label' => 'Document #1'
						],
						[
							'document' => [
	                            'id' => $runtime->getCapture()->get('createPdf1.id'),
	                            'token' => $runtime->getCapture()->get('createPdf1.token')
	                        ],
	                        'label' => 'Document #2'
						]
                    ],
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'comment' => 'Test comment',
                    'document' => null,
					'comparables' => [
						[
							'address' => 'Address 3',
							'salesPrice' => null,
							'closedDate' => null,
							'livingArea' => null,
							'siteSize' => null,
							'actualAge' => null,
							'distanceToSubject' => null,
							'sourceData' => null,
							'comment' => null
						]
					],
					'documents' => [
						[
	                        'id' => new Dynamic(Dynamic::INT),
	                        'type' => null,
	                        'label' => 'Document #1',
	                        'document' => $runtime->getCapture()->get('createPdf'),
	                        'createdAt' => new Dynamic(Dynamic::DATETIME)
	                    ],
						[
	                        'id' => new Dynamic(Dynamic::INT),
	                        'type' => null,
	                        'label' => 'Document #2',
	                        'document' => $runtime->getCapture()->get('createPdf1'),
	                        'createdAt' => new Dynamic(Dynamic::DATETIME)
	                    ]
					],
					'createdAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},

	'getAllWithSecond' => function(Runtime $runtime){

		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [$capture->get('create1'), $capture->get('create2'), $capture->get('create3WithMinimum'), $capture->get('create4WithDocuments')]
			]
		];
	},
];