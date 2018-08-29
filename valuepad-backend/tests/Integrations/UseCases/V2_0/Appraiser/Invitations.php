<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Response;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Core\Invitation\Enums\Requirement;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Invitation\Entities\Invitation;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

return [
	'createW9:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'createResume:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'createSampleReport:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'createEoDocument:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],

	'createAppraiser:init' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => 'appraiserinvitationtest',
			'password' => 'password',
			'w9' => [
				'id' => $capture->get('createW9.id'),
				'token' => $capture->get('createW9.token')
			],
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'dummy',
					'state' => 'TX'
				],
			],
			'eo' => [
				'document' => [
					'id' => $capture->get('createEoDocument.id'),
					'token' => $capture->get('createEoDocument.token')
				]
			]
		]);

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'includes' => ['qualifications'],
				'body' => $data
			]
		];
	},

	'getAscAppraiser:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /asc',
				'parameters' => [
					'search' => [
						'licenseNumber' => $capture->get('createAppraiser.qualifications.primaryLicense.number')
					],
					'filter' => [
						'licenseState' => $capture->get('createAppraiser.qualifications.primaryLicense.state.code')
					]
				]
			]
		];
	},

	'signinAppraiser:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'appraiserinvitationtest',
				'password' => 'password'
			]
		]
	],

	'createCustomer1:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => 'customertest1',
				'password' => 'password',
				'name' => 'customertest1'
			]
		]
	],

	'createCustomer2:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => 'customertest2',
				'password' => 'password',
				'name' => 'customertest2'
			]
		]
	],

	'signinCustomer1:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'customertest1',
				'password' => 'password'
			]
		]
	],
	'signinCustomer2:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'customertest2',
				'password' => 'password'
			]
		]
	],

	'updateSettings1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$config = $runtime->getConfig();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$capture->get('createCustomer1.id').'/settings',
				'body' => [
					'pushUrl' => $config->get('app.url').'/debug/push'
				],
				'auth' => 'guest',
				'headers' => ['Token' => $capture->get('signinCustomer1.token')]
			]
		];
	},

	'updateSettings2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$config = $runtime->getConfig();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$capture->get('createCustomer2.id').'/settings',
				'body' => [
					'pushUrl' => $config->get('app.url').'/debug/push'
				],
				'auth' => 'guest',
				'headers' => ['Token' => $capture->get('signinCustomer2.token')]
			]
		];
	},

	'invite1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/invitations',
				'body' => [
					'ascAppraiser' => $capture->get('getAscAppraiser.0.id'),
					'requirements' => [Requirement::ACH, Requirement::SAMPLE_REPORTS, Requirement::RESUME],
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer1.token')
				]
			]
		];

	},
	'invite2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/invitations',
				'body' => [
					'ascAppraiser' => $capture->get('getAscAppraiser.0.id'),
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer2.token')
				]
			]
		];

	},

	'get1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations/'.$capture->get('invite1.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('invite1.id'),
					'customer' => [
						'id' => $capture->get('createCustomer1.id')
					],
					'appraiser' => [
						'id' => $capture->get('createAppraiser.id')
					],
					'status' => 'pending',
					'requirements' => [Requirement::ACH, Requirement::SAMPLE_REPORTS, Requirement::RESUME],
				],
				'filter' => new ItemFieldsFilter([
					'id', 'customer.id', 'appraiser.id', 'status', 'requirements'
				], true)
			]
		];
	},
	'get2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations/'.$capture->get('invite2.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('invite2.id'),
					'customer' => [
						'id' => $capture->get('createCustomer2.id')
					],
					'appraiser' => [
						'id' => $capture->get('createAppraiser.id')
					],
					'status' => 'pending',
					'requirements' => []
				],
				'filter' => new ItemFieldsFilter([
					'id', 'customer.id', 'appraiser.id', 'status', 'requirements'
				], true)
			]
		];
	},
	'getAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('invite2.id'),
					'customer' => [
						'id' => $capture->get('createCustomer2.id')
					],
					'appraiser' => [
						'id' => $capture->get('createAppraiser.id')
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){ return $v['id'] == $capture->get('invite2.id');}),
					new ItemFieldsFilter(['id', 'customer.id', 'appraiser.id'], true)
				]),
				'total' => ['>=', 2]
			]
		];
	},

	'updateCreatedAt:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'raw' => function(EntityManagerInterface $em) use ($capture){
				/**
				 * @var Invitation $i2
				 */
				$i2 = $em->find(Invitation::class, $capture->get('invite2.id'));
				$i2->setCreatedAt(new DateTime('-10 days'));

				/**
				 * @var Invitation $i1
				 */
				$i1 = $em->find(Invitation::class, $capture->get('invite1.id'));
				$i1->setCreatedAt(new DateTime('+10 days'));

				$em->flush();
			}
		];
	},

	'getAllAsc' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations',
				'parameters' => [
					'orderBy' => 'createdAt:asc',
					'perPage' => 1
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('invite2.id')
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){ return true; }),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},

	'getAllDesc' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations',
				'parameters' => [
					'orderBy' => 'createdAt:desc',
					'perPage' => 1
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('invite1.id')
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){ return true; }),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},

	'tryAccept1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite1.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},

	'replaceAch:init' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$capture->get('createAppraiser.id').'/ach',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'bankName' => 'Bank of America',
					'accountNumber' => '12345678901234567890',
					'accountType' => AchAccountType::CHECKING,
					'routing' => '123456789'
				]
			]
		];
	},

	'tryAccept2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite1.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},


	'setResume:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiser.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'qualifications' => [
						'resume' => [
							'id' => $capture->get('createResume.id'),
							'token' => $capture->get('createResume.token')
						]
					]
				]
			]
		];
	},

	'tryAccept3' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite1.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},

	'setSampleReports:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$capture->get('createAppraiser.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'sampleReports' => [
						[
							'id' => $capture->get('createSampleReport.id'),
							'token' => $capture->get('createSampleReport.token')
						]
					]
				]
			]
		];
	},

	'accept' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite1.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'invitation',
						'event' => 'accept',
						'invitation' => $capture->get('invite1.id'),
						'appraiser' => $capture->get('createAppraiser.id')
					]
				]
			]
		];
	},
	'get1Status' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations/'.$capture->get('invite1.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'status' => 'accepted'
				],
				'filter' => new ItemFieldsFilter(['status'], true)
			]
		];
	},
	'decline' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite2.id').'/decline',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'push' => [
				'body' => [
					[
						'type' => 'invitation',
						'event' => 'decline',
						'invitation' => $capture->get('invite2.id')
					]
				]
			]
		];
	},
	'get2Status' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations/'.$capture->get('invite2.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'status' => 'declined'
				],
				'filter' => new ItemFieldsFilter(['status'], true)
			]
		];
	},
	'tryAccept' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite1.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},
	'tryDecline' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite2.id').'/decline',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},

    'createCustomer3:init' => [
        'request' => [
            'url' => 'POST /customers',
            'body' => [
                'username' => 'customertest3',
                'password' => 'password',
                'name' => 'customertest2'
            ]
        ]
    ],

    'signinCustomer3:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => 'customertest3',
                'password' => 'password'
            ]
        ]
    ],

    'addJobType:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer3.id').'/job-types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer3.token')
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
                'url' => 'POST /customers/'.$capture->get('createCustomer3.id').'/clients',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer3.token')
                ],
                'body' => [
                    'name' => 'Wonderful World'
                ]
            ]
        ];
    },

    'createOrder:init' => function(Runtime $runtime){

	    $customer = $runtime->getCapture()->get('createCustomer3');
	    $appraiser = $runtime->getCapture()->get('createAppraiser');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $runtime->getCapture()->get('addClient.id'),
            'clientDisplayedOnReport' => $runtime->getCapture()->get('addClient.id')
        ]);

        $data['jobType'] = $runtime->getCapture()->get('addJobType.id');


        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customer['id'].'/appraisers/'
                    .$appraiser['id'].'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer3.token')
                ],                'includes' => ['invitation'],
                'body' => $data
            ]
        ];
    },

    'getInvitationWithOrder' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/invitations/'.$capture->get('createOrder.invitation.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'body' => [
                    'status' => 'pending'
                ],
                'filter' => new ItemFieldsFilter(['status'], true)
            ]
        ];
    },

    'declineWithOrder' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'
                    .$capture->get('createAppraiser.id').'/invitations/'
                    .$capture->get('createOrder.invitation.id').'/decline',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ]
        ];
    },

    'tryGetOrder' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'status' => 404
            ]
        ];
    },
];
