<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\User\Enums\Status;
use Ascope\QA\Support\Filters\FirstFilter;
$amc = uniqid('amc');
return [
    'validate' => [
        'request' => [
            'url' => 'POST /amcs',
            'auth' => 'guest',
            'body' => [
                'fax' => '414-422-2222'
            ]
        ],
        'response' => [
            'errors' => [
                'companyName' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'username' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'password' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'email' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'address1' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'state' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'city' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'zip' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'phone' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'fax' => [
                    'identifier' => 'format',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ]
            ]
        ]
    ],
    'create' => [
        'request' => [
            'url' => 'POST /amcs',
            'auth' => 'guest',
            'includes' => [
                'address1', 'address2', 'city', 'state', 'zip', 'lenders', 'phone', 'fax', 'status'
            ],
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
        'response' => [
            'body' => [
                'id' => new Dynamic(Dynamic::INT),
                'status' => Status::PENDING,
                'username' => $amc,
                'email' => 'bestamc@ever.org',
                'companyName' => 'Best AMC Ever!',
                'address1' => '123 Wall Str.',
                'address2' => '124B Wall Str.',
                'city' => 'New York',
                'zip' => '44211',
                'state' => [
                    'code' => 'NY',
                    'name' => 'New York'
                ],
                'lenders' => 'VMX, TTT, abc',
                'phone' => '(423) 553-1211',
                'fax' => '(423) 553-1212',
                'displayName' => 'Best AMC Ever!',
                'type' => 'amc'
            ]
        ],
        'emails' => [
            'body' => [
                [
                    'from' => [
                        'bestamc@ever.org' => 'Best AMC Ever!'
                    ],
                    'to' => [
                        'approvals@appraisalscope.com' => null,
                    ],
                    'subject' => 'New AMC Sign Up on ValuePad',
                    'contents' => new Dynamic(function($value) use ($amc) {

                        $data = [
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
                        ];

                        return str_contains($value, array_values($data));
                    })
                ]
            ]
        ]
    ],

    'trySignin1' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $amc,
                'password' => 'password'
            ]
        ],
        'response' => [
            'status' => 422
        ]
    ],

    'declineAmc' => function(Runtime $runtime) use ($amc){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'admin',
                'body' => [
                    'username' => $amc,
                    'status' => Status::DECLINED
                ]
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            'bestamc@ever.org' => 'Best AMC Ever!',
                        ],
                        'subject' => 'Your AMC account has been declined',
                        'contents' => new Dynamic(function($value) {
                            return str_contains($value, 'declined');
                        })
                    ]
                ]
            ]
        ];
    },

    'trySignin2' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $amc,
                'password' => 'password'
            ]
        ],
        'response' => [
            'status' => 422
        ]
    ],

    'approveAmc' => function(Runtime $runtime) use ($amc){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'admin',
                'body' => [
                    'username' => $amc,
                    'status' => Status::APPROVED
                ]
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            'bestamc@ever.org' => 'Best AMC Ever!',
                        ],
                        'subject' => 'Your AMC account has been approved',
                        'contents' => new Dynamic(function($value) {
                            return str_contains($value, 'approved');
                        })
                    ]
                ]
            ]
        ];
    },

    'signin' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $amc,
                'password' => 'password'
            ]
        ],
        'response' => [
            'status' => 200
        ]
    ],

    'get' => function(Runtime $runtime) use ($amc){
        return [
            'request' => [
                'url' => 'GET /amcs/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signin.token'),
                ],
                'includes' => [
                    'address1', 'address2', 'city', 'state', 'zip', 'lenders', 'phone', 'fax', 'status'
                ],
            ],

            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'status' => Status::APPROVED,
                    'username' => $amc,
                    'email' => 'bestamc@ever.org',
                    'companyName' => 'Best AMC Ever!',
                    'address1' => '123 Wall Str.',
                    'address2' => '124B Wall Str.',
                    'city' => 'New York',
                    'zip' => '44211',
                    'state' => [
                        'code' => 'NY',
                        'name' => 'New York'
                    ],
                    'lenders' => 'VMX, TTT, abc',
                    'phone' => '(423) 553-1211',
                    'fax' => '(423) 553-1212',
                    'displayName' => 'Best AMC Ever!',
                    'type' => 'amc'
                ]
            ]
        ];
    },

    'tryGetAll' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /amcs',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signin.token'),
                ],
            ],
            'response' => [
                'status' => 403
            ]
        ];
    },

    'getAll' => [
        'request' => [
            'url' => 'GET /amcs',
            'auth' => 'admin'
        ],
        'response' => [
            'body' => [
                'id' => new Dynamic(Dynamic::INT),
                'username' => new Dynamic(Dynamic::STRING),
                'email' => new Dynamic(Dynamic::STRING),
                'companyName' => new Dynamic(Dynamic::STRING),
                'displayName' => new Dynamic(Dynamic::STRING),
                'type' => 'amc'
            ],
            'filter' => new FirstFilter(function(){ return true; })
        ]
    ],

    'update' => function(Runtime $runtime) use ($amc){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signin.token'),
                ],
                'body' => [
                    'status' => Status::DECLINED,
                    'username' => 'amcxxx',
                    'email' => 'bestamc1@ever.org',
                    'companyName' => 'Best AMC Ever1!',
                    'address1' => '222 Wall Str.',
                    'address2' => '222B Wall Str.',
                    'city' => 'Los Angeles',
                    'zip' => '98222',
                    'state' => 'CA',
                    'lenders' => 'GGG',
                    'phone' => '(423) 333-3333',
                    'fax' => '(423) 666-6677',
                ]
            ],
        ];
    },

    'getUpdated' => function(Runtime $runtime) use ($amc){
        return [
            'request' => [
                'url' => 'GET /amcs/'.$runtime->getCapture()->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signin.token'),
                ],
                'includes' => [
                    'address1', 'address2', 'city', 'state', 'zip', 'lenders', 'phone', 'fax', 'status'
                ],
            ],

            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'status' => Status::APPROVED,
                    'username' => 'amcxxx',
                    'email' => 'bestamc1@ever.org',
                    'companyName' => 'Best AMC Ever1!',
                    'displayName' => 'Best AMC Ever1!',
                    'type' => 'amc',
                    'address1' => '222 Wall Str.',
                    'address2' => '222B Wall Str.',
                    'city' => 'Los Angeles',
                    'zip' => '98222',
                    'state' => [
                        'code' => 'CA',
                        'name' => 'California'
                    ],
                    'lenders' => 'GGG',
                    'phone' => '(423) 333-3333',
                    'fax' => '(423) 666-6677',
                ]
            ]
        ];
    },
];
