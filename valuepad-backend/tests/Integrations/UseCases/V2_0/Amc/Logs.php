<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Core\User\Enums\Status;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
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

    'createOrder1:init' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $capture->get('addClient.id'),
            'clientDisplayedOnReport' => $capture->get('addClient.id')
        ]);

        $data['jobType'] = $capture->get('addJobType.id');

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/amcs/'.$capture->get('createAmc.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => $data
            ],

        ];
    },
    'createOrder2:init' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $capture->get('addClient.id'),
            'clientDisplayedOnReport' => $capture->get('addClient.id')
        ]);

        $data['jobType'] = $capture->get('addJobType.id');

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/amcs/'.$capture->get('createAmc.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => $data
            ],

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

    'getAll' => function(Runtime $runtime){

        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/logs',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'total' => 2
            ]
        ];
    },
    'getOne' => function(Runtime $runtime){

        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/logs/'.$runtime->getCapture()->get('getAll.0.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $runtime->getCapture()->get('getAll.0.id')
                ],
                'filter' => new ItemFieldsFilter(['id'], true)
            ]
        ];
    }
];
