<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\User\Enums\Status;

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


    'syncFee:init' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'data' => [
                        [
                            'jobType' => 1,
                            'amount' => 100,
                        ],
                        [
                            'jobType' => 2,
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
        ];
    },

    'validate1' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => -12,
                        ]
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'data' => [
                        'identifier' => 'collection',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => [
                            [
                                'identifier' => 'dataset',
                                'message' => new Dynamic(Dynamic::STRING),
                                'extra' => [
                                    'amount' => [
                                        'identifier' => 'greater',
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

    'validate2' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => 24,
                        ],
                        [
                            'state' => 'OO',
                            'amount' => 24,
                        ]
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'data' => [
                        'identifier' => 'exists',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'validate3' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => 24,
                        ],
                        [
                            'state' => 'CA',
                            'amount' => 24,
                        ]
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'data' => [
                        'identifier' => 'unique',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'sync1' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => 100,
                        ],
                        [
                            'state' => 'TX',
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'CA',
                            'name' => 'California'
                        ],
                        'amount' => 100,
                    ],
                    [
                        'state' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ],
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'tryUpdate' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'state' => 'CA'
                ]
            ],
            'response' => [
                'errors' => [
                    'state' => [
                        'identifier' => 'already-taken',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => [],
                    ]
                ]
            ]
        ];
    },

    'sync2' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.1');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'state' => 'TX',
                            'amount' => 98,
                        ],
                    ]
                ]
            ]
        ];
    },

    'getAll1' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'CA',
                            'name' => 'California'
                        ],
                        'amount' => 100,
                    ],
                    [
                        'state' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ],
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },
    'getAll2' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.1');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ],
                        'amount' => 98,
                    ],
                ]
            ]
        ];
    },

    'update2' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.1');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'state' => 'NV',
                    'amount' => 200,
                ]
            ]
        ];
    },

    'updateTheSameState' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.1');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/NV',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'state' => 'NV',
                ]
            ]
        ];
    },

    'get2Updated' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.1');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'NV',
                            'name' => 'Nevada'
                        ],
                        'amount' => 200,
                    ],
                ]
            ]
        ];
    },

    'update2Wrong' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.1');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'amount' => 200,
                ]
            ],
            'response' => [
                'status' => 404
            ]
        ];
    },

    'syncFee2:init' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'data' => [
                        [
                            'jobType' => 2,
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
        ];
    },

    'trySync' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => 100,
                        ],
                        [
                            'state' => 'TX',
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
            'response' => [
                'status' => 404
            ]
        ];
    },

    'syncFee3:init' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'data' => [
                        [
                            'jobType' => 1,
                            'amount' => 100,
                        ],
                        [
                            'jobType' => 2,
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
        ];
    },

    'getAll1AfterRestore' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'CA',
                            'name' => 'California'
                        ],
                        'amount' => 100,
                    ],
                    [
                        'state' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ],
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },
];
