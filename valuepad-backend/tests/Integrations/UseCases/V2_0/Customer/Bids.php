<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Tests\Integrations\Support\Filters\MessageAndExtraFilter;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Response;
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;

$ecd = (new DateTime('+1 month'))->format(DateTime::ATOM);
$ecd2 = (new DateTime('+2 month'))->format(DateTime::ATOM);

return [
	'createBidRequest' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$requestBody = OrdersFixture::getAsBidRequest($runtime->getHelper(), ['client' => 1, 'clientDisplayedOnReport' => 2]);

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'includes' => ['processStatus', 'property', 'assignedAt', 'customer'],
				'auth' => 'customer',
				'body' => $requestBody
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'customer' => new Dynamic(function($value){
						return is_array($value);
					}),
					'assignedAt' => null,
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'processStatus' => 'request-for-bid',
					'property' => new Dynamic(function($data){
						return is_array($data) && count($data) != 0;
					})
				]
			],
			'live' => [
				'body' => [
                    'channels' => [
                        'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                    ],
					'event' => 'order:create-log',
					'data' => new Dynamic(function($data){
						return $data['action'] === Action::BID_REQUEST;
					})
				],
				'filter' => new FirstFilter(function($k, $data){
					return $data['event'] === 'order:create-log';
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
								return starts_with($value, 'Bid Request - Order on '.$capture->get('createBidRequest.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
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
								'name' => 'bid-request'
							],
							'message' => new Dynamic(function($value) use ($capture){
								$property = $capture->get('createBidRequest.property');

								return str_contains($value, [
									'a bid request',
									$property['address1'].', '.$property['city'].', '.$property['state']['code'].' '.$property['zip']
								]);
							}),
							'extra' => [
								'order' => $capture->get('createBidRequest.id'),
								'fileNumber' => $capture->get('createBidRequest.fileNumber'),
								'processStatus' => $capture->get('createBidRequest.processStatus')
							]
						]
					]
				];
			}
		];
	},

	'getLogsForBidRequest' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		$capture = $runtime->getCapture();

		$order = $capture->get('createBidRequest');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$order['id'].'/logs',
			],
			'response' => [
				'body' => [
					[
						'id' => new Dynamic(Dynamic::INT),
						'action' => Action::BID_REQUEST,
						'actionLabel' => 'Bid Request',
						'message' => sprintf(
							'You have received a bid request on %s, %s, %s %s from %s.',
							$order['property']['address1'],
							$order['property']['city'],
							$order['property']['state']['code'],
							$order['property']['zip'],
							$order['customer']['name']

						),
						'user' => new Dynamic(function($data) use ($customerSession){
							return $data['id'] == $customerSession->get('user.id');
						}),
						'order' => new Dynamic(function($data) use ($capture){
							return $data['id'] == $capture->get('createBidRequest.id');
						}),
						'extra' => [
							'user' => $customerSession->get('user.name'),
							'customer' => $order['customer']['name'],
							'address1' => $capture->get('createBidRequest.property.address1'),
							'address2' => $capture->get('createBidRequest.property.address2'),
							'city' => $capture->get('createBidRequest.property.city'),
							'zip' => $capture->get('createBidRequest.property.zip'),
							'state' => $capture->get('createBidRequest.property.state'),
						],
						'createdAt' => new Dynamic(Dynamic::DATETIME)
					]
				]
			]
		];
	},

	'validateRequired' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id').'/bid',
				'auth' => 'customer',
				'body' => [
					'comments' => 'test comments'
				]
			],
			'response' => [
				'errors' => [
					'amount' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'validate' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$ecd = (new DateTime('-1 month'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id').'/bid',
				'auth' => 'customer',
				'body' => [
					'amount' => -10,
					'estimatedCompletionDate' => $ecd,
					'comments' => 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddddd
					dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd
					ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd
					dddddddddddddddd'
				]
			],
			'response' => [
				'errors' => [
					'amount' => [
						'identifier' => 'greater',
					],
					'estimatedCompletionDate' => [
						'identifier' => 'greater',
					],
					'comments' => [
						'identifier' => 'length'
					]
				],
				'filter' => new MessageAndExtraFilter()
			]
		];
	},

	'updateBidRequest' => function(Runtime $runtime){

		$session  = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id'),
				'auth' => 'customer',
				'body' => [
					'amcLicenseNumber' => 'XXX-YYY-ZZZ'
				]
			]
		];
	},

	'getBidRequest' => function(Runtime $runtime){

		$session  = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id'),
				'auth' => 'customer',
				'includes' => ['amcLicenseNumber', 'fee']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createBidRequest.id'),
					'fileNumber' => $capture->get('createBidRequest.fileNumber'),
					'amcLicenseNumber' => 'XXX-YYY-ZZZ',
					'fee' => null
				]
			]
		];
	},

	'create' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id').'/bid',
				'auth' => 'customer',
				'body' => [
					'amount' => 20.11
				]
			],
			'push' => [
				'body' => []
			]
		];
	},

	'get' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id').'/bid',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'amount' => 20.11,
					'estimatedCompletionDate' => null,
					'comments' => null
				]
			]
		];
	},

	'award' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id').'/award',
				'auth' => 'customer'
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
                            return $data['action'] === Action::AWARD_ORDER;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:award',
                        'data' => [
                            'id' => $capture->get('createBidRequest.id'),
                            'fileNumber' => $capture->get('createBidRequest.fileNumber'),
                            'processStatus' => ProcessStatus::FRESH
                        ]
                    ]
                ]
            ],

            'mobile' => function(Runtime $runtime){
                $session = $runtime->getSession('appraiser');
                $capture = $runtime->getCapture();

                return [
                    'body' => [
                        [
                            'users' => [$session->get('user.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'award'
                            ],
                            'message' => new Dynamic(function($value) use ($capture){
                                return str_contains($value, 'awarded the bid request on');
                            }),
                            'extra' => [
                                'order' => $capture->get('createBidRequest.id')
                            ]
                        ]
                    ]
                ];
            },

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
                                $session->get('user.email') => $session->get('user.displayName'),
                            ],
                            'subject' => new Dynamic(function($value) use ($capture){
                                return starts_with($value, 'Bid Awarded - Order on ');
                            }),
                            'contents' => new Dynamic(Dynamic::STRING)
                        ]
                    ]
                ];
            },
		];
	},

    'getAwardedLogs' => function(Runtime $runtime){
        $appraiserSession = $runtime->getSession('appraiser');

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
                    .$capture->get('createBidRequest.id').'/logs',
                'parameters' => [
                    'perPage' => 1000
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::AWARD_ORDER
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] == Action::AWARD_ORDER;
                    }),
                    new ItemFieldsFilter(['action'], true)
                ])
            ]
        ];
    },

	'getAwarded' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createBidRequest.id'),
				'includes' => ['fee', 'processStatus', 'estimatedCompletionDate', 'dueDate', 'assignedAt'],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'fee' => 20.11,
					'assignedAt' => new Dynamic(function($value){
						return (new DateTime($value))->format('Y') == (new DateTime())->format('Y');
					}),
					'estimatedCompletionDate' => null,
					'dueDate' => null,
					'processStatus' => 'new'
				]
			]
		];
	},

	'tryGet' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id').'/bid',
				'auth' => 'customer'
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},

	'createBidRequest2:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$requestBody = OrdersFixture::getAsBidRequest($runtime->getHelper(), ['client' => 1, 'clientDisplayedOnReport' => 2]);

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $requestBody
			]
		];
	},

	'create2' => function(Runtime $runtime) use ($ecd) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest2.id').'/bid',
				'auth' => 'customer',
				'body' => [
					'amount' => 1000,
					'estimatedCompletionDate' => $ecd,
					'comments' => 'Some comments'
				]
			],
			'response' => [
				'body' => [
					'amount' => 1000,
					'estimatedCompletionDate' => $ecd,
					'comments' => 'Some comments'
				]
			]
		];
	},

	'update2' => function(Runtime $runtime) use ($ecd2) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();



		return [
			'request' => [
				'url' => 'PATCH /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest2.id').'/bid',
				'auth' => 'customer',
				'body' => [
					'amount' => 300.92,
					'estimatedCompletionDate' => $ecd2,
					'comments' => 'Some comments 2'
				]
			]
		];
	},

	'get2' => function(Runtime $runtime) use ($ecd2) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest2.id').'/bid',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'amount' => 300.92,
					'estimatedCompletionDate' => $ecd2,
					'comments' => 'Some comments 2'
				]
			]
		];
	},

	'award2' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$assignedAt = (new DateTime('2012-01-01 09:23:22'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest2.id').'/award',
				'auth' => 'customer',
				'body' => [
					'assignedAt' => $assignedAt
				]
			],
		];
	},

	'getAwarded2' => function(Runtime $runtime) use ($ecd2){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$dueDate = new DateTime($ecd2);
		$dueDate->modify('+1 day');
		$dueDate = $dueDate->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createBidRequest2.id'),
				'includes' => ['fee', 'processStatus', 'estimatedCompletionDate', 'dueDate', 'assignedAt'],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'fee' => 300.92,
					'assignedAt' => (new DateTime('2012-01-01 09:23:22'))->format(DateTime::ATOM),
					'estimatedCompletionDate' => $ecd2,
					'dueDate' => $dueDate,
					'processStatus' => 'new'
				]
			]
		];
	},
];