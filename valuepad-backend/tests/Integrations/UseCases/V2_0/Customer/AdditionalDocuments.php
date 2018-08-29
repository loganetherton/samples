<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;

return [
	'createOrder:init' => function(Runtime $runtime){
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
				]),
				'includes' => ['property']
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
	'createWithWrongType' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'customer',
				'body' => [
					'type' => 99999,
					'document' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
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
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'customer',
				'body' => [
					'type' => $capture->get('addType1.id'),
					'document' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'type' => $capture->get('addType1'),
					'document' => $capture->get('createPdf'),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'label' => null
				]
			],
			'push' => [
				'body' => []
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
                            return $data['action'] === Action::CREATE_ADDITIONAL_DOCUMENT;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:create-additional-document',
                        'data' => new Dynamic(function($data) {
                            return in_array('order', array_keys($data)) && in_array('additionalDocument', array_keys($data));
                        })
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
								return starts_with($value, 'New Additional Document - Order on '.$capture->get('createOrder.property.address1'));
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
								'name' => 'create-additional-document'
							],
							'message' => new Dynamic(function($value) use ($capture){
								return str_contains($value, $capture->get('createPdf.name'));
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

	'getCreateLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000
				]
			],
			'response' => [
				'body' => [
					'action' => Action::CREATE_ADDITIONAL_DOCUMENT,
					'message' => sprintf(
						'%s has uploaded the "%s" additional document.',
						$customerSession->get('user.name'),
						$capture->get('createPdf.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'name' => $capture->get('create.document.name'),
						'size' => $capture->get('create.document.size'),
						'format' => $capture->get('create.document.format'),
						'url' => $capture->get('create.document.url'),
						'type' => $capture->get('addType1.title')
					]

				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::CREATE_ADDITIONAL_DOCUMENT;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [$capture->get('create')]
			]
		];
	},

	'tryCreateDefault' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'customer',
				'body' => [
					'document' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
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
	'createWithDefault' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'customer',
				'body' => [
					'label' => 'Test Label',
					'document' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'type' => null,
					'document' => $capture->get('createPdf'),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'label' => 'Test Label'
				]
			]
		];
	},
	'getAllWithDefault' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'customer'
			],
			'response' => [
				'body' => $capture->get('createWithDefault'),
				'total' => 2,
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('createWithDefault.id');
				})
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents/'.$capture->get('create.id'),
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
                            return $data['action'] === Action::DELETE_ADDITIONAL_DOCUMENT;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:delete-additional-document',
                        'data' => new Dynamic(function($data) {
                            return in_array('order', array_keys($data)) && in_array('additionalDocument', array_keys($data));
                        })
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
								return starts_with($value, 'Additional Document Deleted - Order on '.$capture->get('createOrder.property.address1'));
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
								'name' => 'delete-additional-document'
							],
							'message' => new Dynamic(function($value) use ($capture){
								return str_contains($value, $capture->get('createPdf.name'));
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

	'getDeleteLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000
				]
			],
			'response' => [
				'body' => [
					'action' => Action::DELETE_ADDITIONAL_DOCUMENT,
					'message' => sprintf(
						'%s has deleted the "%s" additional document.',
						$customerSession->get('user.name'),
						$capture->get('createPdf.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'name' => $capture->get('create.document.name'),
						'size' => $capture->get('create.document.size'),
						'format' => $capture->get('create.document.format'),
						'url' => $capture->get('create.document.url'),
						'type' => $capture->get('addType1.title')
					]

				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::DELETE_ADDITIONAL_DOCUMENT;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getAllAfterDelete' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [$capture->get('createWithDefault')]
			]
		];
	},
];