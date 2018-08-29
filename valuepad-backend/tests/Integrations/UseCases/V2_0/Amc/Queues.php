<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
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

    'createOrder1:init' => function(Runtime $runtime){

        $customer = $runtime->getSession('customer')->get('user');

        $capture = $runtime->getCapture();

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        return [
            'request' => [
                'url' => 'POST /customers/'.$customer['id'].'/amcs/'.$capture->get('createAmc.id').'/orders',
                'auth' => 'customer',
                'body' => $data
            ],

        ];
    },

    'acceptOrder:init' => function(Runtime $runtime){
        $customer = $runtime->getSession('customer')->get('user');

        return [
            'request' => [
                'url' => 'POST /customers/'.$customer['id'].'/orders/'.$runtime->getCapture()->get('createOrder1.id').'/workflow/accepted',
                'auth' => 'customer'
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

    'accepted' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/queues/accepted',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    $runtime->getCapture()->get('createOrder1')
                ]
            ]
        ];
    },

    'inspected' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/queues/inspected',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => []
            ]
        ];
    },

    'counters' => function(Runtime $runtime){
        $amc = $runtime->getCapture()->get('createAmc');
        $session = $runtime->getCapture()->get('signinAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/queues/counters',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'new' => 0,
                    'accepted' => 1,
                    'inspected' => 0,
                    'scheduled' => 0,
                    'onHold' => 0,
                    'late' => 0,
                    'readyForReview' => 0,
                    'completed' => 0,
                    'revision' => 0,
                    'due' => 1,
                    'open' => 1,
                    'all' => 1
                ]
            ]
        ];
    }
];
