<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$appraiser = uniqid('appraiser');
$manager = uniqid('manager');
$manager2 = uniqid('manager');

return [
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
                'document' => __DIR__.'/test.txt'
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
                    'state' => 'CA'
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

    'signinAppraiser' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $appraiser,
                'password' => 'password'
            ]
        ]
    ],

    'createCompany:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'includes' => ['email'],
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'The World Champion Company',
                    'firstName' => 'ayyy',
                    'lastName' => 'lmao',
                    'email' => 'kaori@kaoru.co.jp',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'Ooooooo',
                    'city' => 'Uranus',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => '11-0009111',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'ach' => [
                        'bankName' => 'sadfasdfwe',
                        'accountNumber' => '11122221122',
                        'accountType' => AchAccountType::CHECKING,
                        'routing' => '123221232'
                    ],
                    'w9' => ['id' => $capture->get('createW9.id'), 'token' => $capture->get('createW9.token')],
                    'otherType' => 'Other company type',
                ]
            ],
        ];
    },
    'createBranch:init' => function (Runtime $runtime)  {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'Branching Branch',
                    'taxId' => '97-8888888',
                    'address1' => 'wooooooooooooooooo',
                    'city' => 'Abilene',
                    'state' => 'TX',
                    'zip' => '87545',
                    'assignmentZip' => '15648',
                    'eo' => [
                        'claimAmount' => 220.00,
                        'aggregateAmount' => 11.1,
                        'deductible' => 2.3,
                        'expiresAt' => (new DateTime('+1 month'))->format('c'),
                        'carrier' => 'asdfg',
                        'document' => [
                            'id' => $capture->get('createEoDocument.id'),
                            'token' => $capture->get('createEoDocument.token')
                        ]
                    ]
                ]
            ]
        ];
    },

    'checkRequired' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'includes' => ['branch'],
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'isAdmin' => true
                ]
            ],
            'response' => [
                'errors' => [
                    'user.username' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.password' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.firstName' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.lastName' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.email' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.phone' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'branch' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                ]
            ]
        ];
    },

    'validation' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'includes' => ['branch'],
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'user' => [
                        'username' => '&^$@%^@',
                        'password' => 's',
                        'firstName' => ' ',
                        'lastName' => ' ',
                        'email' => 'testytestgmail.com',
                        'phone' => '999242-2211',
                    ],
                    'branch' => 77777
                ]
            ],
            'response' => [
                'errors' => [
                    'user.username' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.password' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.firstName' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.lastName' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.email' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'user.phone' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'branch' => [
                        'identifier' => 'exists',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'createStaff' => function(Runtime $runtime) use ($manager){
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'includes' => ['branch', 'user.phone'],
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'user' => [
                        'username' => $manager,
                        'password' => 'secret',
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'email' => 'testytest@gmail.com',
                        'phone' => '(999) 242-2211',
                    ],
                    'branch' => $runtime->getCapture()->get('createBranch.id'),
                    'notifyUser' => true,
                    'isManager' => false,
                    'isRfpManager' => true,
                    'isAdmin' => true
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'email' => 'testytest@gmail.com',
                    'phone' => '(999) 242-2211',
                    'user' => [
                        'id' => new Dynamic(Dynamic::INT),
                        'username' => $manager,
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'displayName' => 'Man Ager',
                        'email' => 'testytest@gmail.com',
                        'phone' => '(999) 242-2211',
                        'type' => 'manager'
                    ],
                    'branch' => [
                        'id' => $runtime->getCapture()->get('createBranch.id'),
                        'name' => $runtime->getCapture()->get('createBranch.name')
                    ],
                    'isManager' => true,
                    'isRfpManager' => true,
                    'isAdmin' => true
                ]
            ],

            'emails' => function(Runtime $runtime){

                $capture = $runtime->getCapture();

                return  [
                    'body' => [
                        [
                            'from' => [
                                $capture->get('createCompany.email') => $capture->get('createCompany.name')
                            ],
                            'to' => [
                                'testytest@gmail.com' => 'Man Ager',
                            ],
                            'subject' => 'Manager Account Created',
                            'contents' => new Dynamic(function($value){
                                return str_contains($value, 'secret');
                            })
                        ]
                    ]
                ];
            },
        ];
    },
    'signinManager:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $manager,
                'password' => 'secret'
            ]
        ],
    ],
    'createStaffWithoutNotification' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'includes' => ['branch'],
                'body' => [
                    'user' => [
                        'username' => uniqid('manager'),
                        'password' => 'secret',
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'email' => 'testytest@gmail.com',
                        'phone' => '(999) 242-2211',
                    ],
                    'branch' => $runtime->getCapture()->get('createBranch.id'),
                    'notifyUser' => false
                ]
            ],
            'emails' => []
        ];
    },

    'getManager' => function(Runtime $runtime) use ($manager){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createStaff.user.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'includes' => ['phone', 'availability']
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'username' => $manager,
                    'firstName' => 'Man',
                    'lastName' => 'Ager',
                    'displayName' => 'Man Ager',
                    'email' => 'testytest@gmail.com',
                    'phone' => '(999) 242-2211',
                    'type' => 'manager',
                    'availability' => [
                        'isOnVacation' => false,
                        'from' => null,
                        'to' => null,
                        'message' => '',
                    ],
                ]
            ]
        ];
    },
    'updateManager' => function(Runtime $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'PATCH /managers/' . $runtime->getCapture()->get('createStaff.user.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'username' => $manager2,
                    'password' => 'secret2',
                    'firstName' => 'Man2',
                    'lastName' => 'Ager2',
                    'phone' => '(777) 242-2211',
                    'email' => 'testytestupdated@gmail.com',
                ]
            ]
        ];
    },

    'getManagerUpdated' => function(Runtime $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createStaff.user.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'includes' => ['phone', 'availability']
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'username' => $manager2,
                    'firstName' => 'Man2',
                    'lastName' => 'Ager2',
                    'phone' => '(777) 242-2211',
                    'email' => 'testytestupdated@gmail.com',
                    'displayName' => 'Man2 Ager2',
                    'type' => 'manager',
                    'availability' => [
                        'isOnVacation' => false,
                        'from' => null,
                        'to' => null,
                        'message' => '',
                    ],
                ]
            ]
        ];
    },

    'updateManagerWithAdmin' => function(Runtime $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'PATCH /managers/' . $runtime->getCapture()->get('createStaff.user.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'firstName' => 'Fake',
                    'lastName' => 'Dude',
                ]
            ]
        ];
    },

    'getManagerUpdatedByAdmin' => function(Runtime $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createStaff.user.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'includes' => ['phone', 'availability']
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'username' => $manager2,
                    'firstName' => 'Fake',
                    'lastName' => 'Dude',
                    'phone' => '(777) 242-2211',
                    'email' => 'testytestupdated@gmail.com',
                    'displayName' => 'Fake Dude',
                    'type' => 'manager',
                    'availability' => [
                        'isOnVacation' => false,
                        'from' => null,
                        'to' => null,
                        'message' => '',
                    ],
                ]
            ]
        ];
    },

    'getStaff' => function(Runtime  $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'GET /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createStaff.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'includes' => ['branch', 'user.availability'],
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'email' => 'testytestupdated@gmail.com',
                    'phone' => '(777) 242-2211',
                    'user' => [
                        'id' => new Dynamic(Dynamic::INT),
                        'username' => $manager2,
                        'firstName' => 'Fake',
                        'lastName' => 'Dude',
                        'displayName' => 'Fake Dude',
                        'email' => 'testytestupdated@gmail.com',
                        'type' => 'manager',
                        'availability' => [
                            'isOnVacation' => false,
                            'from' => null,
                            'to' => null,
                            'message' => '',
                        ],
                    ],
                    'branch' => [
                        'id' => $runtime->getCapture()->get('createBranch.id'),
                        'name' => $runtime->getCapture()->get('createBranch.name')
                    ],
                    'isManager' => true,
                    'isRfpManager' => true,
                    'isAdmin' => true
                ]
            ]
        ];
    },
    'createBranch2:init' => function (Runtime $runtime)  {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'Branching Branch 2',
                    'taxId' => '97-0909889',
                    'address1' => 'wooooooooooooooooo',
                    'city' => 'Abilene',
                    'state' => 'TX',
                    'zip' => '87545',
                    'assignmentZip' => '15648',
                    'eo' => [
                        'claimAmount' => 220.00,
                        'aggregateAmount' => 11.1,
                        'deductible' => 2.3,
                        'expiresAt' => (new DateTime('+1 month'))->format('c'),
                        'carrier' => 'asdfg',
                        'document' => [
                            'id' => $capture->get('createEoDocument.id'),
                            'token' => $capture->get('createEoDocument.token')
                        ]
                    ]
                ]
            ]
        ];
    },

    'updateStaff' => function(Runtime  $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'PATCH /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createStaff.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'includes' => ['branch'],
                'body' => [
                    'branch' => $runtime->getCapture()->get('createBranch2.id'),
                    'isManager' => false,
                    'isRfpManager' => false,
                    'isAdmin' => false,
                    'email' => 'overrides@usersemail.com',
                    'phone' => '(111) 000-8765'
                ]
            ]
        ];
    },
    'getStaffUpdated' => function(Runtime  $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'GET /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createStaff.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'includes' => ['branch']
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'email' => 'overrides@usersemail.com',
                    'phone' => '(111) 000-8765',
                    'user' => new Dynamic(function($value) use ($runtime){
                        return $value['id'] == $runtime->getCapture()->get('createStaff.user.id');
                    }),
                    'branch' => [
                        'id' => $runtime->getCapture()->get('createBranch2.id'),
                        'name' => $runtime->getCapture()->get('createBranch2.name')
                    ],
                    'isManager' => true,
                    'isRfpManager' => false,
                    'isAdmin' => false
                ]
            ]
        ];
    },
    'deleteStaff' => function(Runtime  $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'DELETE /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createStaff.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
            ]
        ];
    },
    'getStaffDeleted' => function(Runtime  $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'GET /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createStaff.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'includes' => ['branch']
            ],
            'response' => [
               'status' => 404
            ]
        ];
    },
    'getManagerDeleted' => function(Runtime  $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createStaff.user.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'response' => [
                'status' => 403
            ]
        ];
    },
];