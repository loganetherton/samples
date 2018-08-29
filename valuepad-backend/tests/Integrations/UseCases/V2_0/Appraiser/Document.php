<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use Ascope\QA\Support\Filters\FirstFilter;

$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

return [
	'defineFormats:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/documents/formats',
				'auth' => 'customer',
				'body' => [
					'jobType' => 3,
					'primary' => ['pdf'],
					'extra' => ['zoo']
				]
			]
		];
	},
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
				'body' => $data
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
	'create2ndPdf:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
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
	'create2ndZoo:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.zoo'
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
	'createXml:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.xml'
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
	'completeWithXmlWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'primary' => [
						'id' => $capture->get('createXml.id'),
						'token' => $capture->get('createXml.token')
					]
				]
			],
			'response' => [
				'errors' => [
					'primary' => [
						'identifier' => 'format',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'complete' => function(Runtime $runtime){
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
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'primary' => $capture->get('createPdf'),
					'primaries' => [$capture->get('createPdf')],
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'extra' => [],
					'showToAppraiser' => true
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'create-document',
						'order' => $capture->get('createOrder.id'),
						'document' => new Dynamic(Dynamic::INT)
					],
					[
						'type' => 'order',
						'event' => 'update-process-status',
						'order' => $capture->get('createOrder.id'),
						'oldProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
						'newProcessStatus' => ProcessStatus::READY_FOR_REVIEW
					]
				]
			],
			'emails' => [
				'body' => []
			],
			'mobile' => [
				'body' => []
			],
            'live' => [
                'body' => [
                    'event' => 'order:create-document',
                    'channels' => [
                        'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                    ],
                    'data' => [
                        'order' => [
                            'id' => $capture->get('createOrder.id'),
                            'fileNumber' => $capture->get('createOrder.fileNumber')
                        ],
                        'document' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'primary' => $capture->get('createPdf'),
                            'primaries' => [$capture->get('createPdf')],
                            'createdAt' => new Dynamic(Dynamic::DATETIME),
                            'extra' => [],
                            'showToAppraiser' => true
                        ]
                    ]
                ],
                'filter' => new FirstFilter(function($k, $v){
                    return $v['event'] == 'order:create-document';
                })
            ]
		];
	},

	'email' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document/email',
				'body' => [
					'email' => 'igor.vorobioff@gmail.com'
				]
			],
			'emails' => [
				'body' => [
					[
						'from' => [
							'no-reply@valuepad.com' => $session->get('user.firstName').' '.$session->get('user.lastName')
						],
						'to' => [
							'igor.vorobioff@gmail.com' => null,
						],
						'subject' => 'Documents - Order#: '.$capture->get('createOrder.fileNumber'),
						'contents' => new Dynamic(function($value) use ($session, $capture){
							return str_contains($value, $session->get('user.firstName').' '.$session->get('user.lastName'))
							&& str_contains($value, $capture->get('createOrder.fileNumber'))
							&& str_contains($value, $capture->get('createPdf.name'))
							&& str_contains($value, $capture->get('createPdf.url'));
						})
					]
				]
			]
		];
	},

	'getCompletedOrder' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'ready-for-review'
				]
			]
		];
	},
	'getDocument' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document'
			],
			'response' => [
				'body' => $capture->get('complete')
			]
		];
	},
	'updatePrimaryWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'primary' => [
						'id' => $capture->get('create2ndPdf.id'),
						'token' => $capture->get('create2ndPdf.token')
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
	'addUnsupportedZapExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'extra' => [
						[
							'id' => $capture->get('createZap.id'),
							'token' => $capture->get('createZap.token')
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
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'extra' => [
						[
							'id' => $capture->get('createZoo.id'),
							'token' => $capture->get('createZoo.token')
						]
					]
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'update-document',
						'order' => $capture->get('createOrder.id'),
						'document' => $capture->get('complete.id')
					],
				]
			],
            'mobile' => [
                'body' => []
            ],
            'email' => [
                'body' => []
            ],
            'live' => [
                'body' => [
                    'event' => 'order:update-document',
                    'channels' => [
                        'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                    ],
                    'data' => [
                        'order' => [
                            'id' => $capture->get('createOrder.id'),
                            'fileNumber' => $capture->get('createOrder.fileNumber')
                        ],
                        'document' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'primary' => $capture->get('createPdf'),
                            'primaries' => [$capture->get('createPdf')],
                            'createdAt' => new Dynamic(Dynamic::DATETIME),
                            'extra' => new Dynamic(function($value){
                                return !!$value;
                            }),
                            'showToAppraiser' => true
                        ]
                    ]
                ],
                'filter' => new FirstFilter(function($k, $v){
                    return $v['event'] == 'order:update-document';
                })
            ]
		];
	},
	'getDocumentWithZooExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document'
			],
			'response' => [
				'body' => [
					'extra' => [
						$capture->get('createZoo')
					]
				],
				'filter' => new ItemFieldsFilter(['extra'], true)
			]
		];
	},
	'updateFormats' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id').'/settings/documents/formats/'.$capture->get('defineFormats.id'),
				'auth' => 'customer',
				'body' => [
					'extra' => ['zoo', 'aci']
				]
			]
		];
	},
	'addAciExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'extra' => [
						$capture->get('createZoo.id'),
						[
							'id' => $capture->get('createAci.id'),
							'token' => $capture->get('createAci.token')
						]
					]
				]
			]
		];
	},
	'add2ndZooExtraWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'extra' => [
						$capture->get('createZoo.id'),
						[
							'id' => $capture->get('create2ndZoo.id'),
							'token' => $capture->get('create2ndZoo.token')
						],
						$capture->get('createAci.id'),
					]
				]
			],
			'response' => [
				'errors' => [
					'extra' => [
						'identifier' => 'unique',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'replace1stWith2ndZooExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'extra' => [
						[
							'id' => $capture->get('create2ndZoo.id'),
							'token' => $capture->get('create2ndZoo.token')
						],
						$capture->get('createAci.id'),
					]
				]
			]
		];
	},
	'getDocumentWithZooAndAciExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document'
			],
			'response' => [
				'body' => [
					'extra' => [
						$capture->get('create2ndZoo'),
						$capture->get('createAci')
					]
				],
				'filter' => new ItemFieldsFilter(['extra'], true)
			]
		];
	},
	'removeZooFormat:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id')
					.'/settings/documents/formats/'.$capture->get('defineFormats.id'),
				'auth' => 'customer',
				'body' => [
					'extra' => ['zoo']
				]
			]
		];
	},

	'updateTheSame' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'extra' => [
						$capture->get('create2ndZoo.id'),
						$capture->get('createAci.id'),
					]
				]
			]
		];
	},

	'create2nd' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'body' => [
					'primary' => [
						'id' => $capture->get('create2ndPdf.id'),
						'token' => $capture->get('create2ndPdf.token')
					],
					'extra' => [
						[
							'id' => $capture->get('create2ndZoo.id'),
							'token' => $capture->get('create2ndZoo.token')
						]
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'primary' => $capture->get('create2ndPdf'),
					'primaries' => [$capture->get('create2ndPdf')],
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'extra' => [
						$capture->get('create2ndZoo')
					],
					'showToAppraiser' => true
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'create-document',
						'order' => $capture->get('createOrder.id'),
						'document' => new Dynamic(function($v) use ($capture){
							return $capture->get('complete.id') != $v;
						})
					]
				]
			]
		];
	},
	'get2ndDocument' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document'
			],
			'response' => [
				'body' => $capture->get('create2nd')
			]
		];
	},

	'createRevision:init' => function(Runtime $runtime){
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
			]
		];
	},
	'completeRevision' => function(Runtime $runtime){
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
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'primary' => $capture->get('createPdf'),
					'primaries' => [$capture->get('createPdf')],
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'extra' => [],
					'showToAppraiser' => true
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'create-document',
						'order' => $capture->get('createOrder.id'),
						'document' => new Dynamic(Dynamic::INT)
					],
					[
						'type' => 'order',
						'event' => 'update-process-status',
						'order' => $capture->get('createOrder.id'),
						'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
						'newProcessStatus' => ProcessStatus::REVISION_IN_REVIEW
					]
				]
			]
		];
	},
	'getOrderWithCompletedRevision' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'revision-in-review'
				]
			]
		];
	},


    'completeByCustomer:init' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/workflow/completed',
                'auth' => 'customer'
            ]
        ];
    },

    'tryCompleteWhenCompleted' => function(Runtime $runtime){
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
            ],
            'response' => [
                'status' => 400
            ]
        ];
    },
];