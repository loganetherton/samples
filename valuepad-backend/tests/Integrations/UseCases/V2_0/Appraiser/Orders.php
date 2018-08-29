<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ArrayFieldsFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$newCustomer = [
	'username' => uniqid('customer'),
	'password' => 'asouidhfasodf'
];

$newAppraiser = [
	'username' => uniqid('appraiser'),
	'password' => 'woeiufhwoueif'
];

return [
	'createOrder1:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['techFee'] = 10.02;

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
	'createOrder2:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

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

	'createOrder3:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

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

	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING)
				],
				'filter' => new FirstFilter(function($k, $v) use ($capture) {
					return true;
				})
			]
		];
	},
	'get' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder1.id'),
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING)
				]
			]
		];
	},
	'accept' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/accept',
			],
			'push' => [
				'body' => [
					'type' => 'order',
					'event' => 'update-process-status',
					'order' => $capture->get('createOrder1.id'),
					'oldProcessStatus' => ProcessStatus::FRESH,
					'newProcessStatus' => ProcessStatus::ACCEPTED
				],
				'single' => true
			],
			'live' => [
				'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::UPDATE_PROCESS_STATUS;
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
								return $data['id'] == $capture->get('createOrder1.id');
							}),
							'oldProcessStatus' => ProcessStatus::FRESH,
							'newProcessStatus' => ProcessStatus::ACCEPTED,
						]
					]
				]
			],
			'emails' => [
				'body' => []
			],
			'mobile' => [
				'body' => []
			]
		];
	},

	'getAccepted' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder1.id'),
				'includes' => ['processStatus', 'acceptedAt']
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'processStatus' => 'accepted',
					'acceptedAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},
	'acceptAgain' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/accept',
			]
		];
	},
	'tryDecline' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/decline',
				'body' => [
					'reason' => 'out-of-coverage-area'
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},
	'declineValidation' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder2.id').'/decline',
			],
			'response' => [
				'errors' => [
					'reason' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'decline' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder2.id').'/decline',
				'body' => [
					'reason' => 'other',
					'message' => 'some message'
				]
			],
			'push' => [
				'body' => [
					'type' => 'order',
					'event' => 'decline',
					'order' => $capture->get('createOrder2.id'),
					'reason' => 'other',
					'message' => 'some message'
				],
				'single' => true
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:decline',
						'data' => new Dynamic(function($data){
							return is_array($data);
						}),
					],
				]
			]
		];
	},
	'getDeclined' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder2.id')
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},

	'validateAcceptWithConditions1' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder3.id').'/accept-with-conditions',
				'body' => [
					'request' => 'fee-increase-and-due-date-extension',
					'fee' => -10.2,
					'dueDate' => (new DateTime('-2 years'))->format(DateTime::ATOM),
					'explanation' => 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddddd
					dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd
					ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd
					dddddddddddddddd'
				],
			],
			'response' => [
				'errors' => [
					'fee' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'validateAcceptWithConditions2' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder3.id').'/accept-with-conditions',
				'body' => [
					'request' => 'fee-increase',
					'dueDate' => (new DateTime('+2 years'))->format(DateTime::ATOM),
					'explanation' => 'test'
				],
			],
			'response' => [
				'errors' => [
					'fee' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'validateAcceptWithConditions3' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder3.id').'/accept-with-conditions',
				'body' => [
					'request' => 'due-date-extension',
					'fee' => 100,
					'explanation' => 'test'
				],
			],
			'response' => [
				'errors' => [
					'dueDate' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'validateAcceptWithConditions4' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder3.id').'/accept-with-conditions',
				'body' => [
					'request' => 'other',
				],
			],
			'response' => [
				'errors' => [
					'explanation' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'acceptWithConditions' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$dueDate = (new DateTime('+2 years'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder3.id').'/accept-with-conditions',
				'body' => [
					'request' => 'fee-increase-and-due-date-extension',
					'fee' => 100.01,
					'dueDate' => $dueDate,
					'explanation' => 'The project is too large.'
				],
			],
			'push' => [
				'body' => [
					'type' => 'order',
					'event' => 'accept-with-conditions',
					'order' => $capture->get('createOrder3.id'),
					'conditions' => [
						'request' => 'fee-increase-and-due-date-extension',
						'fee' => 100.01,
						'dueDate' => $dueDate,
						'explanation' => 'The project is too large.'
					]
				],
				'single' => true
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:accept-with-conditions',
						'data' => new Dynamic(function($data){
							return is_array($data);
						}),
					],
				]
			]
		];
	},

	'getAcceptedWithConditions' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder3.id'),
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},

	'createBidRequest' => function(Runtime $runtime){
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

	'declineBidRequest' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createBidRequest.id').'/decline',
				'body' => [
					'reason' => 'other',
					'message' => 'some message'
				]
			],
			'push' => [
				'body' => [
					'type' => 'order',
					'event' => 'decline',
					'order' => $capture->get('createBidRequest.id'),
					'reason' => 'other',
					'message' => 'some message'
				],
				'single' => true
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:decline',
						'data' => new Dynamic(function($data){
							return is_array($data);
						}),
					],
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

	'getTechFeeNotPaid' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder1.id'),
				'includes' => ['isTechFeePaid']
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'isTechFeePaid' => false
				]
			]
		];
	},

	'tryCompleteOrder1' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/document',
				'body' => [
					'primary' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},
	'payTechFee' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/pay-tech-fee',
			],
			'push' => [
				'body' => [
					'type' => 'order',
					'event' => 'pay-tech-fee',
					'order' => $capture->get('createOrder1.id')
				],
				'single' => true
			]
		];
	},
	'completeOrder1' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/document',
				'body' => [
					'primary' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			]
		];
	},
	'checkTinAtCompletion' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id'),
				'body' => [
					'tinAtCompletion' => '555-32-3322'
				],
				'filter' => new ItemFieldsFilter(['tinAtCompletion'], true)
			]
		];
	},
	'payTechFeeAgain' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/pay-tech-fee',
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},

	'getTechFeePaid' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder1.id'),
				'includes' => ['isTechFeePaid']
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'fileNumber' => new Dynamic(Dynamic::STRING),
					'isTechFeePaid' => true
				]
			]
		];
	},

	'createCustomer:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => array_merge($newCustomer, ['name' => 'uggghhh'])
		]
	],

	'signinCustomer:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => $newCustomer
		]
	],

	'createW91:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createEoDocument1:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],

	'createAppraiser:init' => function(Runtime $runtime) use ($newAppraiser) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $newAppraiser['username'],
            'password' => $newAppraiser['password'],
            'w9' => [
                'id' => $capture->get('createW91.id'),
                'token' => $capture->get('createW91.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'TX'
                ],
            ],
            'eo' => [
                'document' => [
                    'id' => $capture->get('createEoDocument1.id'),
                    'token' => $capture->get('createEoDocument1.token')
                ]
            ]
        ]);

        for ($i = 1; $i <=7; $i++){
            $data['eo']['question'.$i] = false;
        }

        return [
            'request' => [
                'url' => 'POST /appraisers',
                'body' => $data
            ]
        ];
    },

    'signinAppraiser:init' => [
    	'request' => [
    		'url' => 'POST /sessions',
    		'body' => $newAppraiser
    	]
    ],

    'relateWithCustomer:init' => function (Runtime $runtime) {
    	return [
    		'raw' => function (EntityManagerInterface $em) use ($runtime) {
    			$customer = $em->find(Customer::class, $runtime->getSession('customer')->get('user.id'));
    			$customer1 = $em->find(Customer::class, $runtime->getCapture()->get('createCustomer.id'));
    			$appraiser = $em->find(Appraiser::class, $runtime->getCapture()->get('createAppraiser.id'));

    			$customer->addAppraiser($appraiser);
    			$customer1->addAppraiser($appraiser);

    			$em->flush();
    		}
    	];
    },

	'createOrder4:init' => function (Runtime $runtime) {
		$capture = $runtime->getCapture();
		$customerSession = $runtime->getSession('customer');
		$appraiser = $capture->get('createAppraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiser['id'].'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'addClient:init' => function(Runtime $runtime) {
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

	'addJobType:init' => function(Runtime $runtime) {
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

	'createOrder5:init' => function (Runtime $runtime) {
		$capture = $runtime->getCapture();
		$customer = $capture->get('signinCustomer');
		$appraiser = $capture->get('createAppraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClient.id'),
			'clientDisplayedOnReport' => $capture->get('addClient.id')
		]);

		$data['jobType'] = $capture->get('addJobType.id');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customer['user']['id'].'/appraisers/'
					.$appraiser['id'].'/orders',
				'auth' => 'guest',
				'headers' => [
					'Token' => $customer['token']
				],
				'body' => $data
			]
		];
	},

	'changeSettings:init' => function (Runtime $runtime) {
		$capture = $runtime->getCapture();
		$customer = $capture->get('signinCustomer');

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$customer['user']['id'].'/settings',
				'auth' => 'guest',
				'headers' => [
					'Token' => $customer['token']
				],
				'body' => [
					'removeAccountingData' => true
				]
			]
		];
	},

	'getAccountingWithoutNewCustomersOrders' => function (Runtime $runtime) {
		$capture = $runtime->getCapture();
		$appraiser = $capture->get('signinAppraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiser['user']['id'].'/orders/accounting',
				'parameters' => ['filter' => ['isPaid' => 'false']],
				'auth' => 'guest',
				'headers' => [
					'Token' => $appraiser['token']
				]
			],
			'response' => [
				'body' => [
					['id' => $capture->get('createOrder4.id')]
				],
				'filter' => new ArrayFieldsFilter(['id'], true),
			]
		];
	}
];
