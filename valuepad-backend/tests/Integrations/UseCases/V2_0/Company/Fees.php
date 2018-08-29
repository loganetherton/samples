<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$appraiser = uniqid('appraiser');
$manager = uniqid('manager');

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

    'signinAppraiser:init' => [
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
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'The World Appraisal Company',
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
                    'taxId' => '11-1111112',
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
                    'taxId' => '11-1111112',
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

    'createManager:init' => function(Runtime $runtime) use ($manager){
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
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
                    'branch' => $runtime->getCapture()->get('createBranch.id')
                ]
            ],
        ];
    },

    'signinManager:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $manager,
                'password' => 'secret'
            ]
        ]
    ],

    'create' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/fees',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'jobType' => 3,
                    'amount' => 50,
                ]
            ],
            'response' => [
                'body' => [
                    'jobType' => ['id' => 3],
                    'amount' => 50,
                ],
                'filter' => new ItemFieldsFilter(['jobType.id', 'amount'], true)
            ]
        ];
    },

    'trySync1' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id').'/fees',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'data' => [
                        [
                            'jobType' => 99999,
                            'amount' => 10
                        ],
                        [
                            'jobType' => 2,
                            'amount' => 10
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

    'trySync2' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id').'/fees',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'data' => [
                        [
                            'jobType' => 1,
                            'amount' => 10
                        ],
                        [
                            'jobType' => 2,
                            'amount' => 10
                        ],
                        [
                            'jobType' => 1,
                            'amount' => 10
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

    'trySync3' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id').'/fees',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'data' => [
                        [
                            'jobType' => 2
                        ],
                        [
                            'jobType' => 1,
                            'amount' => -19
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
                            [
                                'identifier' => 'dataset',
                                'message' => new Dynamic(Dynamic::STRING),
                                'extra' => [
                                    'amount' => [
                                        'identifier' => 'required',
                                        'message' => new Dynamic(Dynamic::STRING),
                                        'extra' => []
                                    ]
                                ]
                            ],
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

    'sync' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id').'/fees',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'data' => [
                        [
                            'jobType' => 1,
                            'amount' => 10
                        ],
                        [
                            'jobType' => 2,
                            'amount' => 20
                        ]
                    ]
                ]
            ],
        ];
    },

    'getAll' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /companies/'.$runtime->getCapture()->get('createCompany.id').'/fees',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'jobType' => new Dynamic(function($data){
                            return $data['id'] == 1;
                        }),
                        'amount' => 10
                    ],
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'jobType' => new Dynamic(function($data){
                            return $data['id'] == 2;
                        }),
                        'amount' => 20
                    ]
                ]
            ]
        ];
    }
];
