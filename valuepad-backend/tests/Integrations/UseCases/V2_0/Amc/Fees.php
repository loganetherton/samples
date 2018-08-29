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

    'validate1' => function(Runtime $runtime){
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
                            'amount' => -10
                        ],
                        [
                            'jobType' => 9999,
                            'amount' => 56
                        ],
                    ]
                ]
            ],

            'response' => [
                'errors' => [
                    'data' => [
                        'identifier' => 'collection',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => [
                            0 => [
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
                            'amount' => 10,
                        ],
                        [
                            'jobType' => 9999,
                            'amount' => 56,
                        ],
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
                            'amount' => 10,
                        ],
                        [
                            'jobType' => 5,
                            'amount' => 56,
                        ],
                        [
                            'jobType' => 1,
                            'amount' => 11,
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
                        [
                            'jobType' => 5,
                            'amount' => 10,
                        ],
                    ]
                ]
            ],

            'response' => [
                'body' => [
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 1;}),
                        'amount' => 100,
                    ],
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 2;}),
                        'amount' => 56,
                    ],
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 5;}),
                        'amount' => 10,
                    ],
                ]
            ]
        ];
    },

    'getAll1' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
               'body' => [
                   [
                       'jobType' => new Dynamic(function($value){ return $value['id'] == 1;}),
                       'amount' => 100,
                   ],
                   [
                       'jobType' => new Dynamic(function($value){ return $value['id'] == 2;}),
                       'amount' => 56,
                   ],
                   [
                       'jobType' => new Dynamic(function($value){ return $value['id'] == 5;}),
                       'amount' => 10,
                   ],
               ]
            ]
        ];
    },

    'sync2' => function(Runtime $runtime){
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
                            'jobType' => 5,
                            'amount' => 100,
                        ],
                        [
                            'jobType' => 1,
                            'amount' => 10,
                        ],
                    ]
                ]
            ],

            'response' => [
                'body' => [
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 1;}),
                        'amount' => 10,
                    ],
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 5;}),
                        'amount' => 100,
                    ],
                ]
            ]
        ];
    },

    'getAll2' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 1;}),
                        'amount' => 10,
                    ],
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 5;}),
                        'amount' => 100,
                    ],
                ]
            ]
        ];
    },


    'sync3' => function(Runtime $runtime){
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
                            'amount' => 73,
                        ],
                    ]
                ]
            ],

            'response' => [
                'body' => [
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 1;}),
                        'amount' => 100,
                    ],
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 2;}),
                        'amount' => 73,
                    ],
                ]
            ]
        ];
    },

    'getAll3' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/fees',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 1;}),
                        'amount' => 100,
                    ],
                    [
                        'jobType' => new Dynamic(function($value){ return $value['id'] == 2;}),
                        'amount' => 73,
                    ],
                ]
            ]
        ];
    },
];
