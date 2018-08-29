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
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/NV/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'zip' => '89019',
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
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/NV/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                       [
                            'zip' => '89019',
                            'amount' => 55,
                       ],
                        [
                            'zip' => '22222',
                            'amount' => 100,
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
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/NV/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'zip' => '89019',
                            'amount' => 55,
                        ],
                        [
                            'zip' => '89019',
                            'amount' => 5555,
                        ],
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
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/CA/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'zip' => '94132',
                            'amount' => 100,
                        ],
                        [
                            'zip' => '94106',
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '94132',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '94106',
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'sync2' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'zip' => '75075',
                            'amount' => 100,
                        ],
                        [
                            'zip' => '75424',
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '75075',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '75424',
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'syncForeign' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.1');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'zip' => '75076',
                            'amount' => 100,
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
                'url' => 'GET /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/CA/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '94132',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '94106',
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'getAll2' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '75075',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '75424',
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'sync3' => function(Runtime $runtime) {
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'data' => [
                        [
                            'zip' => '75075',
                            'amount' => 552,
                        ],
                    ]
                ]
            ]
        ];
    },
    'getAll3' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $fee = $runtime->getCapture()->get('syncFee.0');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees/'.$fee['jobType']['id'].'/states/TX/zips',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '75075',
                        'amount' => 552,
                    ],
                ]
            ]
        ];
    },
];
