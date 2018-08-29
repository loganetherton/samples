<?php
use Ascope\QA\Support\Response;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Api\Session\V2_0\Protectors\AutoLoginProtector;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;

$appraiser = uniqid('appraiser');
$customer = uniqid('customer');

return [
    'createAsAppraiser' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => 'appraiser',
                'password' => 'password'
            ],
        ]
    ],
    'validation' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => 'wrongappraiser',
                'password' => 'password',
            ],
            'auth' => 'guest'
        ],
        'response' => [
            'errors' => [
                'credentials' => [
                    'identifier' => 'access',
                    'message' => 'The user with the provided credentials cannot be found.',
					'extra' => []
                ]
            ]
        ]
    ],

    'createW9:init' => [
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
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createAppraiser:init' => function(Runtime $runtime) use ($appraiser){

        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser,
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
                'body' => $data
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

    'create' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $appraiser,
                'password' => 'password'
            ],
            'auth' => 'guest',
        ],
        'response' => [
           'body' => [
               'id' => new Dynamic(Dynamic::INT),
               'token' => new Dynamic(Dynamic::STRING),
               'createdAt' => new Dynamic(Dynamic::DATETIME),
               'expireAt' => new Dynamic(Dynamic::DATETIME),
               'user' => [
                   'id' => new Dynamic(Dynamic::INT),
                   'firstName' => 'Jack',
                   'lastName' => 'Black',
                   'displayName' => 'Jack Black',
                   'username' => $appraiser,
                   'email' => 'jack.black@test.org',
                   'type' => 'appraiser'
               ]
           ]
        ]
    ],
    'get' => function(Runtime $runtime) use ($appraiser){
        $capture = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /sessions/'.$capture->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create.token')
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('create.id'),
                    'token' => $capture->get('create.token'),
                    'createdAt' => $capture->get('create.createdAt'),
                    'expireAt' => $capture->get('create.expireAt'),
                    'user' => [
                        'id' => new Dynamic(Dynamic::INT),
                        'firstName' => 'Jack',
                        'lastName' => 'Black',
                        'displayName' => 'Jack Black',
                        'username' => $appraiser,
                        'email' => 'jack.black@test.org',
                        'type' => 'appraiser'
                    ]
                ]
            ]
        ];
    },
    'create2nd' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $appraiser,
                'password' => 'password'
            ],
            'auth' => 'guest',
        ]
    ],
    'get2nd' => function(Runtime $runtime){
        $capture  = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /sessions/'.$capture->get('create2nd.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create.token')
                ]
            ]
        ];
    },
    'create3rd' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $customer,
                'password' => 'password'
            ],
            'auth' => 'guest',
        ]
    ],
    'get3rd' => function(Runtime $runtime){
        $capture  = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /sessions/'.$capture->get('create3rd.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create3rd.token')
                ]
            ]
        ];
    },
    'getWrong3rd' => function(Runtime $runtime){
        $capture  = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /sessions/'.$capture->get('create3rd.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create.token')
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },
    'deleteWrong1stAnd2nd' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'DELETE /sessions',
                'parameters' => ['user' => $capture->get('create.user.id')],
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create3rd.token')
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },
    'delete3rd' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'DELETE /sessions/'.$capture->get('create3rd.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create3rd.token')
                ]
            ]
        ];
    },
    'deleted3rd' => function(Runtime $runtime){
        $capture  = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /sessions/'.$capture->get('create3rd.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create3rd.token')
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },
    'delete1stAnd2nd' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'DELETE /sessions',
                'parameters' => ['user' => $capture->get('create.user.id')],
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create2nd.token')
                ]
            ]
        ];
    },
    'deleted1st' => function(Runtime $runtime){
        $capture  = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /sessions/'.$capture->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create.token')
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },
    'deleted2nd' => function(Runtime $runtime){
        $capture  = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /sessions/'.$capture->get('create2nd.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('create2nd.token')
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },
    'createForRefresh' => [
        'request' => [
            'url' => 'POST /sessions',
            'auth' => 'guest',
            'body' => [
                'username' => $appraiser,
                'password' => 'password',
            ],
        ]
    ],
    'refresh' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /sessions/'.$capture->get('createForRefresh.id').'/refresh',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('createForRefresh.token')
                ]
            ],
            'response' => [
                'assert' => function(Response $response) use ($capture){
                    $body = $response->getBody();

                    if ($body['token'] == $capture->get('createForRefresh.token')){
                        return false;
                    }

                    $newExpireAt = new DateTime($body['expireAt']);
                    $oldExpireAt = new DateTime($capture->get('createForRefresh.expireAt'));

                    if ($newExpireAt < $oldExpireAt){
                        return false;
                    }

                    return true;
                }
            ]
        ];
    },
	'tryCreateAutoLoginTokenWithoutToken' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /sessions/auto-login-tokens',
				'auth' => 'customer',
				'body' => [
					'user' => $appraiserSession->get('user.id')
				]
			],
			'response' => [
				'status' => Response::FORBIDDEN
			]
		];
	},

	'tryCreateAutoLoginToken' => [
        'request' => [
            'url' => 'POST /sessions/auto-login-tokens',
            'auth' => 'customer',
            'headers' => [
                AutoLoginProtector::HEADER => AutoLoginProtector::TOKEN
            ],
            'body' => [
                'user' => 15141412
            ]
        ],
        'response' => [
            'errors' => [
                'user' => [
                    'identifier' => 'user-not-found',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ]
            ]
        ]
    ],

	'createAutoLoginToken' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /sessions/auto-login-tokens',
				'auth' => 'customer',
				'headers' => [
					AutoLoginProtector::HEADER => AutoLoginProtector::TOKEN
				],
				'body' => [
					'user' => $appraiserSession->get('user.id')
				]
			],
			'response' => [
				'body' => [
					'token' => new Dynamic(Dynamic::STRING)
				]
			]
		];
	},
	'loginWithAutoLoginToken' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /sessions',
				'auth' => 'guest',
				'body' => [
					'autoLoginToken' => $capture->get('createAutoLoginToken.token')
				]
			],
			'response' => [
				'body' => [
					'user' => [
						'id' => $appraiserSession->get('user.id')
					]
				],
				'filter' => new ItemFieldsFilter(['user.id'], true)
			]
		];
	},
	'tryLoginWithAutoLoginTokenAgain' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /sessions',
				'auth' => 'guest',
				'body' => [
					'autoLoginToken' => $capture->get('createAutoLoginToken.token')
				]
			],
			'response' => [
				'errors' => [
					'autoLoginToken' => [
						'identifier' => 'invalid',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	}
];
