<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Response;

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
                    'taxId' => '09-4504507',
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
                    'taxId' => '09-4504507',
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

    'createOrder1:init' => function(Runtime $runtime){

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

    'createOrder2:init' => function(Runtime $runtime){

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

    'signinManager:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $manager,
                'password' => 'secret'
            ]
        ]
    ],

    'create1' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'content' => 'Hello Appraiser!'
                ]
            ],
            'live' => [
                'body' => [
                    'channels' => [
                        'private-user-'.$capture->get('createAppraiser.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$capture->get('createAppraiser.id'),
                        'private-user-'.$runtime->getCapture()->get('createManager.user.id')

                    ],
                    'event' => 'order:send-message',
                    'data' => [
                        'id' => new Dynamic(Dynamic::INT),
                        'sender' => ['id' => $capture->get('createAppraiser.id')],
                        'content' => 'Hello Appraiser!',
                        'order' => $capture->get('createOrder1'),
                        'createdAt' => new Dynamic(Dynamic::DATETIME)
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $data){
                        return $data['event'] === 'order:send-message';
                    }),
                    new ItemFieldsFilter([
                        'channels', 'event','data.id',
                        'data.sender.id', 'data.content', 'data.order',
                        'data.createdAt', 'data.employee'], true)
                ])
            ],
            'emails' => [
                'body' => []
            ],
            'mobile' => [
                'body' => []
            ]
        ];
    },
    'get1' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $message = $capture->get('create1');
        $message['isRead'] = false;

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id')
                    .'/messages/'.$capture->get('create1.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => $message
            ]
        ];
    },

    'create2' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /managers/'.$capture->get('createManager.user.id').'/orders/'
                    .$capture->get('createOrder2.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
                'body' => [
                    'content' => 'Hello Manager!'
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'send-message',
                        'order' => $capture->get('createOrder2.id'),
                        'message' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    'channels' => [
                        'private-user-'.$capture->get('createAppraiser.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$capture->get('createAppraiser.id'),
                        'private-user-'.$runtime->getCapture()->get('createManager.user.id')
                    ],
                    'event' => 'order:send-message',
                    'data' => [
                        'id' => new Dynamic(Dynamic::INT),
                        'sender' => ['id' => $runtime->getCapture()->get('createManager.user.id')],
                        'content' => 'Hello Manager!',
                        'order' => $capture->get('createOrder2'),
                        'createdAt' => new Dynamic(Dynamic::DATETIME)
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $data){
                        return $data['event'] === 'order:send-message';
                    }),
                    new ItemFieldsFilter([
                        'channels', 'event','data.id',
                        'data.sender.id', 'data.content', 'data.order',
                        'data.createdAt', 'data.employee'], true)
                ])
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
                                $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.displayName'),
                            ],
                            'subject' => new Dynamic(function($value) use ($capture){
                                return starts_with($value, 'Message - Order on ');
                            }),
                            'contents' => new Dynamic(function($value){
                                return str_contains($value, 'Man Ager');
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
                                'name' => 'send-message'
                            ],
                            'message' => new Dynamic(function($value) use ($capture){
                                return str_contains($value, 'sent a message');
                            }),
                            'extra' => [
                                'order' => $capture->get('createOrder2.id')
                            ]
                        ]
                    ]
                ];
            }
        ];
    },

    'get2' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $message = $capture->get('create2');

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id')
                    .'/messages/'.$capture->get('create2.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => $message
            ]
        ];
    },

    'getAllRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/messages',
                'parameters' => [
                    'filter' => [
                        'isRead' => 'true'
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'assert' => function(Response $response){
                    $body = $response->getBody();

                    if (!$body){
                        return false;
                    }

                    foreach ($body as $row){
                        if ($row['isRead'] === false){
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ];
    },

    'getAllUnread' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/messages',
                'parameters' => [
                    'filter' => [
                        'isRead' => 'true'
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'assert' => function(Response $response){
                    $body = $response->getBody();

                    if (!$body){
                        return false;
                    }

                    foreach ($body as $row){
                        if ($row['isRead'] === false){
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ];
    },
    'getTotal1' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'total' => 2,
                    'unread' => 1
                ]
            ]
        ];
    },
    'markAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /managers/'.$capture->get('createManager.user.id').'/messages/'
                    .$capture->get('create1.id').'/mark-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ]
        ];
    },

    'getTotal2' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'total' => 2,
                    'unread' => 0
                ]
            ]
        ];
    },

    'create3:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'content' => 'Hello Appraiser!'
                ]
            ],
        ];
    },

    'markOneAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /managers/'.$capture->get('createManager.user.id').'/messages/'.$capture->get('create3.id').'/mark-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ]
            ]
        ];
    },

    'getTotal3' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'total' => 3,
                    'unread' => 0
                ]
            ]
        ];
    },

    'create4:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'content' => 'Hello Appraiser!'
                ]
            ],
        ];
    },

    'markAllAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /managers/'.$capture->get('createManager.user.id').'/messages/mark-all-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ]
            ]
        ];
    },

    'getTotal4' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'total' => 4,
                    'unread' => 0
                ]
            ]
        ];
    },

    'create5:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'content' => 'Hello Appraiser!'
                ]
            ],
        ];
    },

    'markSelectedAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /managers/'.$capture->get('createManager.user.id').'/messages/mark-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
                'body' => [
                    'messages' => [$capture->get('create5.id')]
                ]
            ]
        ];
    },

    'getTotal5' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'total' => 5,
                    'unread' => 0
                ]
            ]
        ];
    },

    'getByOrder1' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder1.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'total' => 4
            ]
        ];
    },

    'getByOrder2' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder2.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'total' => 1
            ]
        ];
    },
];
