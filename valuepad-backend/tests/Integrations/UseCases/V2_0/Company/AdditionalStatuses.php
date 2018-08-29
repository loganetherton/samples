<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;

$appraiser = uniqid('appraiser');
$manager = uniqid('manager');
$customer = uniqid('customer');

return [
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
    'signinCustomer:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $customer,
                'password' => 'password'
            ]
        ]
    ],

    'updateSettings:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $config = $runtime->getConfig();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$capture->get('createCustomer.id').'/settings',
                'body' => [
                    'pushUrl' => $config->get('app.url').'/debug/push'
                ],
                'auth' => 'guest',
                'headers' => ['Token' => $capture->get('signinCustomer.token')]
            ]
        ];
    },

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
                    'taxId' => '09-4504301',
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
                    'taxId' => '09-4504301',
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

    'addJobType:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
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
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/clients',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'name' => 'Wonderful World'
                ]
            ]
        ];
    },


    'createOrder:init' => function(Runtime $runtime){

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $runtime->getCapture()->get('addClient.id'),
            'clientDisplayedOnReport' => $runtime->getCapture()->get('addClient.id')
        ]);

        $data['jobType'] = $runtime->getCapture()->get('addJobType.id');

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getCapture()->get('createCustomer.id').'/companies/'
                    .$runtime->getCapture()->get('createCompany.id').'/staff/'
                    .$runtime->getCapture()->get('getAppraiserStaff.0.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'includes' => ['property'],
                'body' => $data
            ]
        ];
    },

    'accept:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /appraisers/'
                    .$runtime->getCapture()->get('createAppraiser.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/accept',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
            ]
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

    'createAdditionalStatus:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/settings/additional-statuses',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'title' => 'Customer 1 Additional Status'
                ],
            ]
        ];

    },

    'getAll' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id').'/additional-statuses',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [$runtime->getCapture()->get('createAdditionalStatus')]
            ]
        ];
    },

    'changeAdditionalStatus' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'POST /managers/'.$runtime->getCapture()->get('createManager.user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id').'/change-additional-status',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'additionalStatus' => $runtime->getCapture()->get('createAdditionalStatus.id')
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'change-additional-status',
                        'order' => $runtime->getCapture()->get('createOrder.id'),
                        'oldAdditionalStatus' => null,
                        'oldAdditionalStatusComment' => null,
                        'newAdditionalStatus' => $runtime->getCapture()->get('createAdditionalStatus.id'),
                        'newAdditionalStatusComment' => null
                    ]
                ]
            ],

            'live' => [
                'body' => [
                    'event' => 'order:change-additional-status',
                    'channels' => [
                        'private-user-'.$runtime->getCapture()->get('createAppraiser.id'),
                        'private-user-'.$runtime->getCapture()->get('createCustomer.id').'-as-'.$runtime->getCapture()->get('createAppraiser.id'),
                        'private-user-'.$runtime->getCapture()->get('createManager.user.id'),
                    ],
                    'data' => [
                        'order' => [
                            'id' => $runtime->getCapture()->get('createOrder.id'),
                            'fileNumber' => $runtime->getCapture()->get('createOrder.fileNumber')
                        ],
                        'oldAdditionalStatus' => null,
                        'oldAdditionalStatusComment' => null,
                        'newAdditionalStatus' => $runtime->getCapture()->get('createAdditionalStatus'),
                        'newAdditionalStatusComment' => null
                    ]
                ],
                'filter' => new FirstFilter(function($k, $v){
                    return $v['event'] === 'order:change-additional-status';
                })
            ],

            'emails' => function(Runtime $runtime){

                $capture = $runtime->getCapture();

                return  [
                    'body' => [
                        [
                            'from' => [
                                'no-reply@valuepad.com' => 'The ValuePad Team'
                            ],
                            'to' => [
                                $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.firstName')
                                    .' '.$runtime->getCapture()->get('createAppraiser.lastName'),
                            ],
                            'subject' => new Dynamic(function($value) use ($capture){
                                return starts_with($value, $capture->get('createAdditionalStatus.title').' - Order on '.$capture->get('createOrder.property.address1'));
                            }),
                            'contents' => new Dynamic(function($value) use ($capture){
                                return is_string($value) && $value !== null
                                && str_contains($value, 'Current Additional Status: '.$capture->get('createAdditionalStatus.title'));
                            })
                        ]
                    ]
                ];
            },

            'mobile' => function(Runtime $runtime){
                $capture = $runtime->getCapture();

                return [
                    'body' => [
                        [
                            'users' => [$runtime->getCapture()->get('createAppraiser.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'change-additional-status'
                            ],
                            'message' => new Dynamic(function($value) use ($capture){
                                return str_contains($value, $capture->get('createAdditionalStatus.title'));
                            }),
                            'extra' => [
                                'order' => $capture->get('createOrder.id')
                            ]
                        ]
                    ]
                ];
            }
        ];
    },
];
