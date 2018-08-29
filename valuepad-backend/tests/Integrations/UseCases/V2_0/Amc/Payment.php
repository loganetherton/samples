<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Core\Payment\Enums\AccountType;
use Ascope\QA\Integrations\Checkers\Dynamic;

$amc = uniqid('amc');

return [
    'createAmc:init' => [
        'request' => [
            'url' => 'POST /amcs',
            'auth' => 'guest',
            'body' => [
                'username' => $amc,
                'password' => 'password',
                'email' => 'bestamc@ever.org',
                'companyName' => 'Best AMC Ever!',
                'address1' => '123 Wall Str.',
                'address2' => '124B Wall Str.',
                'city' => 'New York',
                'zip' => '44211',
                'state' => 'NY',
                'lenders' => 'VMX, TTT, abc',
                'phone' => '(423) 553-1211',
                'fax' => '(423) 553-1212'
            ]
        ],
    ],

    'approveAmc:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc.id'),
                'auth' => 'admin',
                'body' => [
                    'status' => Status::APPROVED
                ]
            ]
        ];
    },

    'signinAmc:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $amc,
                'password' => 'password'
            ]
        ]
    ],
    'getEmptyCreatedCreditCard' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/payment/credit-card',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'number' => null,
                    'address' => null,
                    'city' => null,
                    'state' => null,
                    'zip' => null
                ]
            ]
        ];
    },
    'createCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /amcs/'.$capture->get('createAmc.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAmc.token')
				],
				'body' => [
					'number' => '1111111111111116',
					'expiresAt' => [
						'month' => 10,
						'year' => (int) (new DateTime('+5 years'))->format('Y')
					],
					'code' => '241'
				]
			],
			'response' => [
				'body' => [
					'number' => '1116',
                    'address' => '123 Wall Str.',
                    'city' => 'New York',
                    'zip' => '44211',
                    'state' => [
                        'code' => 'NY',
                        'name' => 'New York'
                    ],
				]
			]
		];
	},
	'getCreatedCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAmc.token')
				],
			],
			'response' => [
				'body' => [
					'number' => '1116',
                    'address' => '123 Wall Str.',
                    'city' => 'New York',
                    'zip' => '44211',
                    'state' => [
                        'code' => 'NY',
                        'name' => 'New York'
                    ],
				]
			]
		];
	},
	'updateCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PUT /amcs/'.$capture->get('createAmc.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAmc.token')
				],
				'body' => [
					'number' => '1111111111111114',
					'expiresAt' => [
						'month' => 10,
						'year' => (int) (new DateTime('+5 years'))->format('Y')
					],
					'code' => '241',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => 'CA',
				]
			],
			'response' => [
				'body' => [
					'number' => '1114',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
				]
			]
		];
	},
	'getUpdatedCreditCard' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/payment/credit-card',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAmc.token')
				],
			],
			'response' => [
				'body' => [
					'number' => '1114',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ]
				]
			]
		];
	},

    'getEmptyBankAccount' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    'accountType' => null,
                    'routingNumber' => null,
                    'accountNumber' => null,
                    'nameOnAccount' => null,
                    'bankName' => null,
                    'address' => null,
                    'city' => null,
                    'state' => null,
                    'zip' => null
                ]
            ]
        ];
    },

    'validateRequired' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'accountType' => AccountType::CHECKING,
                ]
            ],
            'response' => [
                'errors' => [
                    'routingNumber' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'accountNumber' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'nameOnAccount' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'bankName' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'validate' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'routingNumber' => '02000021',
                    'accountNumber' => '9900000002342353f',
                    'nameOnAccount' => ' ',
                    'bankName' => ' '
                ]
            ],
            'response' => [
                'errors' => [
                    'accountType' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'routingNumber' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'accountNumber' => [
                        'identifier' => 'numeric',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'nameOnAccount' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'bankName' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'createBankAccount' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'accountType' => AccountType::CHECKING,
                    'routingNumber' => '021000021',
                    'accountNumber' => '9900000002',
                    'nameOnAccount' => 'John Connor',
                    'bankName' => 'World Best Bank'
                ]
            ],
            'response' => [
                'body' => [
                    'accountType' => AccountType::CHECKING,
                    'routingNumber' => '0021',
                    'accountNumber' => '0002',
                    'nameOnAccount' => 'John Connor',
                    'bankName' => 'World Best Bank',
                    'address' => '123 Wall Str.',
                    'city' => 'New York',
                    'zip' => '44211',
                    'state' => [
                        'code' => 'NY',
                        'name' => 'New York'
                    ],
                ]
            ]
        ];
    },

    'getBankAccount' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    'accountType' => AccountType::CHECKING,
                    'routingNumber' => '0021',
                    'accountNumber' => '0002',
                    'nameOnAccount' => 'John Connor',
                    'bankName' => 'World Best Bank',
                    'address' => '123 Wall Str.',
                    'city' => 'New York',
                    'zip' => '44211',
                    'state' => [
                        'code' => 'NY',
                        'name' => 'New York'
                    ],
                ]
            ]
        ];
    },

    'replaceBankAccount' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'accountType' => AccountType::SAVINGS,
                    'routingNumber' => '021000022',
                    'accountNumber' => '9900000003',
                    'nameOnAccount' => 'Johnny Depp',
                    'bankName' => 'World Perfect Bank',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => 'CA',
                ]
            ],
            'response' => [
                'body' => [
                    'accountType' => AccountType::SAVINGS,
                    'routingNumber' => '0022',
                    'accountNumber' => '0003',
                    'nameOnAccount' => 'Johnny Depp',
                    'bankName' => 'World Perfect Bank',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
                ]
            ]
        ];
    },

    'getUpdatedBankAccount' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    'accountType' => AccountType::SAVINGS,
                    'routingNumber' => '0022',
                    'accountNumber' => '0003',
                    'nameOnAccount' => 'Johnny Depp',
                    'bankName' => 'World Perfect Bank',
                    'address' => '22 Wall Str.',
                    'city' => 'San Francisco',
                    'zip' => '94122',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
                ]
            ]
        ];
    },
];
