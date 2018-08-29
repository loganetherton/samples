<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Core\Log\Enums\Action;

$customer = uniqid('customer');

return [
	'createPdf1:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],
	'createPdf2:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],
	'createOrder:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/amcs/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
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
	'addJobType:init' => function(Runtime $runtime){
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
	'createOrder2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$amcSession = $runtime->getSession('amc');

		$data =  OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClient.id'),
			'clientDisplayedOnReport' => $capture->get('addClient.id')
		]);

		$data['jobType'] = $capture->get('addJobType.id');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$capture->get('createCustomer.id').'/amcs/'
					.$amcSession->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => $data
			]
		];
	},
	'addType1:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test type'
				]
			]
		];
	},
	'addType2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id')
					.'/settings/additional-documents/types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test type'
				]
			]
		];
	},
	'createWithWrongType' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'amc',
				'body' => [
					'type' => $capture->get('addType2.id'),
					'document' => [
						'id' => $capture->get('createPdf1.id'),
						'token' => $capture->get('createPdf1.token')
					]
				]
			],
			'response' => [
				'errors' => [
					'type' => [
						'identifier' => 'exists',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'create' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'amc',
				'body' => [
					'type' => $capture->get('addType1.id'),
					'document' => [
						'id' => $capture->get('createPdf1.id'),
						'token' => $capture->get('createPdf1.token')
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'type' => $capture->get('addType1'),
					'document' => $capture->get('createPdf1'),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'label' => null
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'create-additional-document',
						'order' => $capture->get('createOrder.id'),
						'additionalDocument' => new Dynamic(Dynamic::INT)
					]
				]
			],
			'emails' => [
				'body' => [],
			],
			'mobile' => [
				'body' => []
			],
            'live' => [
                'body' => [
                    [
                        'channels' => ['private-user-'.$runtime->getSession('amc')->get('user.id')],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::CREATE_ADDITIONAL_DOCUMENT;
                        })
                    ],
                    [
                        'event' => 'order:create-additional-document',
                        'channels' => ['private-user-'.$runtime->getSession('amc')->get('user.id')],
                        'data' => [
                            'order' => [
                                'id' => $capture->get('createOrder.id'),
                                'fileNumber' => $capture->get('createOrder.fileNumber')
                            ],
                            'additionalDocument' => [
                                'id' => new Dynamic(Dynamic::INT),
                                'type' => $capture->get('addType1'),
                                'document' => $capture->get('createPdf1'),
                                'createdAt' => new Dynamic(Dynamic::DATETIME),
                                'label' => null
                            ]
                        ]
                    ]
                ]
            ]
		];
	},

	'getAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'amc',
			],
			'response' => [
				'body' => [$capture->get('create')]
			]
		];
	},
	'tryCreateWithDefaultType' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'amc',
				'body' => [
					'document' => [
						'id' => $capture->get('createPdf2.id'),
						'token' => $capture->get('createPdf2.token')
					]
				]
			],
			'response' => [
				'errors' => [
					'label' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'createWithDefaultType' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'amc',
				'body' => [
					'label' => 'Test Label',
					'document' => [
						'id' => $capture->get('createPdf2.id'),
						'token' => $capture->get('createPdf2.token')
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'type' => null,
					'label' => 'Test Label',
					'document' => $capture->get('createPdf2'),
					'createdAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},
	'getAllWithDefault' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'amc',
			],
			'response' => [
				'body' => $capture->get('createWithDefaultType'),
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $capture->get('createWithDefaultType.id') == $v['id'];
				})
			]
		];
	},

	'getTypes' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder2.id').'/additional-documents/types',
				'auth' => 'amc',
			],
			'response' => [
				'body' => [$capture->get('addType2')]
			]
		];
	}
];