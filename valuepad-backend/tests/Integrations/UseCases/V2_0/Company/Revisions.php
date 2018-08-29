<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

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
                    'taxId' => '11-1111111',
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
                    'taxId' => '11-1111111',
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

    'getAppraiserStaff:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /companies/'.$runtime->getCapture()->get('createCompany.id').'/staff',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
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

    'addPermissions:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createManager.id').'/permissions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'data' => [
                        $runtime->getCapture()->get('getAppraiserStaff.0.id')
                    ]
                ]
            ]
        ];
    },

    'createOrder:init' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$runtime->getCapture()->get('createCompany.id').'/staff/'
                    .$runtime->getCapture()->get('getAppraiserStaff.0.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
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
                    'message' => 'Test Message'
                ]
            ]
        ];
    },

    'createReconsideration:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        $closedDate1 = (new DateTime('+1 days'))->format(DateTime::ATOM);
        $closedDate2 = (new DateTime('+2 days'))->format(DateTime::ATOM);

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/reconsiderations',
                'auth' => 'customer',
                'body' => [
                    'comment' => 'Test comment 1',
                    'comparables' => [
                        [
                            'address' => 'Address 1',
                            'salesPrice' => 1.1,
                            'closedDate' => $closedDate1,
                            'livingArea' => 'Some area to live 1',
                            'siteSize' => 'Large 1',
                            'actualAge' => 'old 1',
                            'distanceToSubject' => 'Long 1',
                            'sourceData' => 'Some source 1',
                            'comment' => 'Some comment 1'
                        ],
                        [
                            'address' => 'Address 2',
                            'salesPrice' => 2.2,
                            'closedDate' => $closedDate2,
                            'livingArea' => 'Some area to live 2',
                            'siteSize' => 'Large 2',
                            'actualAge' => 'old 2',
                            'distanceToSubject' => 'Long 2',
                            'sourceData' => 'Some source 2',
                            'comment' => 'Some comment 2'
                        ]
                    ]
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

    'getAllRevisions' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/revisions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [$runtime->getCapture()->get('createRevision')]
            ]
        ];
    },

    'getAllReconsiderations' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/reconsiderations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [$runtime->getCapture()->get('createReconsideration')]
            ]
        ];
    },
];
