<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

$amc = uniqid('amc');
$customer = uniqid('customer');

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

    'addPushUrl:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc.id').'/settings',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAmc.token')
                ],
                'body' => [
                    'pushUrl' => env('BASE_URL').'/debug/push?_tag=amc'
                ]
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

    'signinCustomer:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $customer,
                'password' => 'password'
            ]
        ]
    ],

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

    'createOrder' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $runtime->getCapture()->get('addClient.id'),
            'clientDisplayedOnReport' => $runtime->getCapture()->get('addClient.id')
        ]);

        $data['jobType'] = $runtime->getCapture()->get('addJobType.id');

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getCapture()->get('createCustomer.id').'/amcs/'
                    .$amc['id'].'/orders',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => $data
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create',
                        'order' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'createBidRequest' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');

        $data = OrdersFixture::getAsBidRequest($runtime->getHelper(), [
            'client' => $runtime->getCapture()->get('addClient.id'),
            'clientDisplayedOnReport' => $runtime->getCapture()->get('addClient.id')
        ]);

        $data['jobType'] = $runtime->getCapture()->get('addJobType.id');

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getCapture()->get('createCustomer.id').'/amcs/'
                    .$amc['id'].'/orders',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => $data
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'bid-request',
                        'order' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },
    'submitBid:init' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $capture = $runtime->getCapture();

        $ecd = (new DateTime('+1 month'))->format(DateTime::ATOM);

        return [
            'request' => [
                'url' => 'POST /amcs/'
                    .$amc['id'].'/orders/'
                    .$capture->get('createBidRequest.id').'/bid',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $session['token']
                ],
                'body' => [
                    'amount' => 1000,
                    'estimatedCompletionDate' => $ecd,
                    'comments' => 'Some comments'
                ]
            ]
        ];
    },

    'award' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createBidRequest.id').'/award',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'award',
                        'order' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },


    'changeAdditionalStatus' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$capture->get('createOrder.id').'/change-additional-status',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => [
                    'additionalStatus' => $runtime->getCapture()->get('createAdditionalStatus.id'),
                    'comment' => 'The additional status 1'
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'change-additional-status',
                        'order' => new Dynamic(Dynamic::INT),
                        'oldAdditionalStatus' => null,
                        'oldAdditionalStatusComment' => null,
                        'newAdditionalStatus' => $runtime->getCapture()->get('createAdditionalStatus.id'),
                        'newAdditionalStatusComment' => 'The additional status 1',
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'createPdf:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createAdditionalDocument' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/additional-documents',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => [
                    'label' => 'test',
                    'document' => [
                        'id' => $runtime->getCapture()->get('createPdf.id'),
                        'token' => $runtime->getCapture()->get('createPdf.token')
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-additional-document',
                        'order' => new Dynamic(Dynamic::INT),
                        'additionalDocument' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },
    'createDocument' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/documents',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => [
                    'primary' => [
                        'id' => $runtime->getCapture()->get('createPdf.id'),
                        'token' => $runtime->getCapture()->get('createPdf.token')
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-document',
                        'order' => new Dynamic(Dynamic::INT),
                        'document' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'deleteAdditionalDocument' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'DELETE /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/additional-documents/'.$runtime->getCapture()->get('createAdditionalDocument.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'delete-additional-document',
                        'order' => new Dynamic(Dynamic::INT),
                        'additionalDocument' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'createZap:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.zap'
            ]
        ]
    ],

    'updateDocument' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/documents/'.$runtime->getCapture()->get('createDocument.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => [
                    'extra' => [
                        [
                            'id' => $runtime->getCapture()->get('createZap.id'),
                            'token' => $runtime->getCapture()->get('createZap.token')
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'update-document',
                        'order' => new Dynamic(Dynamic::INT),
                        'document' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'deleteDocument' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'DELETE /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/documents/'.$runtime->getCapture()->get('createDocument.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'delete-document',
                        'order' => new Dynamic(Dynamic::INT),
                        'document' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'sendMessage' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => [
                    'content' => 'Hello',
                    'employee' => 'Somebody'
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'send-message',
                        'order' => new Dynamic(Dynamic::INT),
                        'message' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'createRevision:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/orders/'
                    .$capture->get('createOrder.id').'/revisions',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'message' => 'Test Message'
                ]
            ],

            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'revision-request',
                        'order' => new Dynamic(Dynamic::INT),
                        'revision' => new Dynamic(Dynamic::INT),
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'createReconsideration:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/orders/'
                    .$capture->get('createOrder.id').'/reconsiderations',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'comment' => 'Test comment 2',
                    'comparables' => [
                        [
                            'address' => 'Address 3',
                            'salesPrice' => 1.1,
                            'closedDate' => (new DateTime('+1 days'))->format(DateTime::ATOM),
                            'livingArea' => 'Some area to live 3',
                            'siteSize' => 'Large 3',
                            'actualAge' => 'old 3',
                            'distanceToSubject' => 'Long 3',
                            'sourceData' => 'Some source 3',
                            'comment' => 'Some comment 3'
                        ]
                    ]
                ]
            ],

            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'reconsideration-request',
                        'order' => new Dynamic(Dynamic::INT),
                        'reconsideration' => new Dynamic(Dynamic::INT),
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'updateOrder' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => [
                    'fileNumber' => 'TTTAAA0000'
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'update',
                        'order' => new Dynamic(Dynamic::INT),
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'updateProcessStatus' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/workflow/late',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => new Dynamic(Dynamic::INT),
                        'oldProcessStatus' => 'revision-pending',
                        'newProcessStatus' => 'late'
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  new Dynamic(Dynamic::INT),
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'deleteOrder' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'DELETE /customers/'.$runtime->getCapture()->get('createCustomer.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ],
            'push' => [
                'body' => [
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'delete',
                        'order' => new Dynamic(Dynamic::INT),
                    ],
                    [
                        '_tag' => 'amc',
                        'type' => 'order',
                        'event' => 'create-log',
                        'order' =>  null,
                        'log' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },
];
