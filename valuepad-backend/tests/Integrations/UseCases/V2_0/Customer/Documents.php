<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;

return [
	'createOrder:init' => function(Runtime $runtime) {
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['jobType'] = 5;

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $data,
				'includes' => ['property']
			]
		];
	},
	'createXml:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.xml'
			]
		]
	],
	'createPdf:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],
	'createAci:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.aci'
			]
		]
	],
	'createZoo:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.zoo'
			]
		]
	],
	'createZap:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.zap'
			]
		]
	],
	'create' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
						.$capture->get('createOrder.id').'/documents',
				'auth' => 'customer',
				'body' => [
					'primary' => [
						'id' => $capture->get('createXml.id'),
						'token' => $capture->get('createXml.token')
					],
					'extra' => [
						[
							'id' => $capture->get('createAci.id'),
							'token' => $capture->get('createAci.token')
						]
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'primary' => new Dynamic(function($data){ return $data['format'] === 'pdf'; }),
					'primaries' => new Dynamic(function($data){
						$data = array_map(function($data){ return $data['format']; }, $data);

						return in_array('pdf', $data) && in_array('xml', $data) && count($data) === 2;
					}),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'extra' => [$capture->get('createAci')],
					'showToAppraiser' => true
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
                            return $data['action'] === Action::CREATE_DOCUMENT;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:create-document',
                        'data' => new Dynamic(function($data) {
                            return in_array('order', array_keys($data)) && in_array('document', array_keys($data));
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
								return starts_with($value, 'New Document - Order on '.$capture->get('createOrder.property.address1'));
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
								'name' => 'create-document'
							],
							'message' => new Dynamic(function($value) use ($capture){
								return str_contains($value, '"test.pdf"');
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
					'action' => Action::CREATE_DOCUMENT,
					'message' => sprintf(
						'%s has uploaded the "test.pdf" document.',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'name' => $capture->get('create.primary.name'),
						'size' => $capture->get('create.primary.size'),
						'format' => $capture->get('create.primary.format'),
						'url' => $capture->get('create.primary.url'),
					]

				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::CREATE_DOCUMENT;
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
					.$capture->get('createOrder.id').'/documents',
				'auth' => 'customer',
			],
			'response' => [
				'body' => [
					[
						'id' => new Dynamic(Dynamic::INT),
						'primary' => new Dynamic(function($data){ return $data['format'] === 'pdf'; }),
						'primaries' => new Dynamic(function($data){
							$data = array_map(function($data){ return $data['format']; }, $data);

							return in_array('pdf', $data) && in_array('xml', $data) && count($data) === 2;
						}),
						'createdAt' => new Dynamic(Dynamic::DATETIME),
						'extra' => [$capture->get('createAci')],
						'showToAppraiser' => true
					]
				]
			]
		];
	},
	'updatePrimaryWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'primary' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			],
			'response' => [
				'errors' => [
					'primary' => [
						'identifier' => 'read-only',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'addUnsupportedPdf' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'extra' => [
						$capture->get('createAci.id'),
						[
							'id' => $capture->get('createPdf.id'),
							'token' => $capture->get('createPdf.token')
						]
					]
				]
			],
			'response' => [
				'errors' => [
					'extra' => [
						'identifier' => 'format',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'addZooExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'extra' => [
						$capture->get('createAci.id'),
						[
							'id' => $capture->get('createZoo.id'),
							'token' => $capture->get('createZoo.token')
						]
					]
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
                            return $data['action'] === Action::UPDATE_DOCUMENT;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:update-document',
                        'data' => new Dynamic(function($data) {
                            return in_array('order', array_keys($data)) && in_array('document', array_keys($data));
                        })
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
                                'name' => 'update-document'
                            ],
                            'message' => new Dynamic(function($value) use ($capture){
                                return str_contains($value, '"test.pdf"');
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
    'getUpdateLogs' => function(Runtime $runtime){
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
                    'action' => Action::UPDATE_DOCUMENT,
                    'message' => sprintf(
                        '%s has updated the "test.pdf" document.',
                        $customerSession->get('user.name')
                    ),
                    'extra' => [
                        'user' => $customerSession->get('user.name'),
                        'name' => $capture->get('create.primary.name'),
                        'size' => $capture->get('create.primary.size'),
                        'format' => $capture->get('create.primary.format'),
                        'url' => $capture->get('create.primary.url'),
                    ]

                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] == Action::UPDATE_DOCUMENT;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },
	'getAllWithExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents',
				'auth' => 'customer',
			],
			'response' => [
				'body' => [
					[
						'id' => new Dynamic(Dynamic::INT),
						'primary' => new Dynamic(function($data){ return $data['format'] === 'pdf'; }),
						'primaries' => new Dynamic(function($data){
							$data = array_map(function($data){ return $data['format']; }, $data);

							return in_array('pdf', $data) && in_array('xml', $data) && count($data) === 2;
						}),
						'createdAt' => new Dynamic(Dynamic::DATETIME),
						'extra' => [$capture->get('createAci'), $capture->get('createZoo')],
						'showToAppraiser' => true
					]
				]
			]
		];
	},
	'create2' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents',
				'auth' => 'customer',
				'body' => [
					'primary' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					],
					'extra' => []
				]
			]
		];
	},
	'get2nd' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents',
				'auth' => 'customer',
			],
			'response' => [
				'total' => 2,
				'body' => $capture->get('create2'),
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create2.id');
				})
			]
		];
	},
	'delete1st' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents/'.$capture->get('create.id'),
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
                            return $data['action'] === Action::DELETE_DOCUMENT;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:delete-document',
                        'data' => new Dynamic(function($data) {
                            return in_array('order', array_keys($data)) && in_array('document', array_keys($data));
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
								return starts_with($value, 'Document Deleted - Order on '.$capture->get('createOrder.property.address1'));
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
								'name' => 'delete-document'
							],
							'message' => new Dynamic(function($value) use ($capture){
								return str_contains($value, '"test.pdf"');
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
					'action' => Action::DELETE_DOCUMENT,
					'message' => sprintf(
						'%s has deleted the "%s" document.',
						$customerSession->get('user.name'),
						$capture->get('createPdf.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'name' => $capture->get('create.primary.name'),
						'size' => $capture->get('create.primary.size'),
						'format' => $capture->get('create.primary.format'),
						'url' => $capture->get('create.primary.url'),
					]

				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] == Action::DELETE_DOCUMENT;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},
	'getAllCheckDelete' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/documents',
				'auth' => 'customer',
			],
			'response' => [
				'total' => 1,
				'body' => $capture->get('create2'),
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create2.id');
				})
			]
		];
	},
];