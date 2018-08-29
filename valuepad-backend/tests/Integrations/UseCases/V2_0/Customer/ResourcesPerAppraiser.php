<?php

use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Support\Filters\ArrayFieldsFilter;
use Ascope\QA\Integrations\Checkers\Dynamic;

$customer1 = uniqid('customer1');
$customer2 = uniqid('customer2');

return [
    'createCustomer1:init' => [
        'request' => [
            'url' => 'POST /customers',
            'body' => [
                'username' => $customer1,
                'password' => 'password',
                'name' => $customer1
            ]
        ]
    ],
    'signinCustomer1:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $customer1,
                'password' => 'password'
            ]
        ]
    ],

    'addClient1:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/clients',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer1.token')
                ],
                'body' => [
                    'name' => 'Wonderful World'
                ]
            ]
        ];
    },

    'addJobType1:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/job-types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer1.token')
                ],
                'body' => [
                    'title' => 'Test 1'
                ]
            ]
        ];
    },

    'createCustomer2:init' => [
        'request' => [
            'url' => 'POST /customers',
            'body' => [
                'username' => $customer2,
                'password' => 'password',
                'name' => $customer2
            ]
        ]
    ],
    'signinCustomer2:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $customer2,
                'password' => 'password'
            ]
        ]
    ],

    'addClient2:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/clients',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer2.token')
                ],
                'body' => [
                    'name' => 'Wonderful World'
                ]
            ]
        ];
    },

    'addJobType2:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/job-types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer2.token')
                ],
                'body' => [
                    'title' => 'Test 1'
                ]
            ]
        ];
    },

    'createOrder1:init' => function(Runtime $runtime){
        $customerSession = $runtime->getCapture()->get('signinCustomer1');
        $appraiserSession = $runtime->getSession('appraiser');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $runtime->getCapture()->get('addClient1.id'),
            'clientDisplayedOnReport' => $runtime->getCapture()->get('addClient1.id')
        ]);

        $data['jobType'] = $runtime->getCapture()->get('addJobType1.id');
        $data['isPaid'] = false;
        $data['techFee'] = 20;
        $data['fee'] = 10;

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customerSession['user']['id'].'/appraisers/'
                    .$appraiserSession->get('user.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ],
                'body' => $data
            ]
        ];
    },

    'createOrder2:init' => function(Runtime $runtime){
        $customerSession = $runtime->getCapture()->get('signinCustomer2');
        $appraiserSession = $runtime->getSession('appraiser');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $runtime->getCapture()->get('addClient2.id'),
            'clientDisplayedOnReport' => $runtime->getCapture()->get('addClient2.id')
        ]);

        $data['jobType'] = $runtime->getCapture()->get('addJobType2.id');
        $data['isPaid'] = true;
        $data['paidAt'] = (new DateTime('-20 days'))->format(DateTime::ATOM);
        $data['techFee'] = 40;
        $data['fee'] = 30;

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customerSession['user']['id'].'/appraisers/'
                    .$appraiserSession->get('user.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer2.token')
                ],
                'body' => $data
            ]
        ];
    },

    'getOrders' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/orders',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'id' => $runtime->getCapture()->get('createOrder1.id'),
                        'fileNumber' => $runtime->getCapture()->get('createOrder1.fileNumber')
                    ]
                ]
            ]
        ];
    },

    'acceptOrder1:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /appraisers/'.$runtime->getSession('appraiser')->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder1.id').'/accept',
            ]
        ];
    },

    'acceptOrder2:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /appraisers/'.$runtime->getSession('appraiser')->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder2.id').'/accept',
            ]
        ];
    },

    'getLogs' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/logs',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'order' => [
                            'id' => $runtime->getCapture()->get('createOrder1.id')
                        ]
                    ],
                    [
                        'order' => [
                            'id' => $runtime->getCapture()->get('createOrder1.id')
                        ]
                    ]
                ],
                'filter' => new ArrayFieldsFilter(['order.id'], true)
            ]
        ];
    },

    'createMessage1:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',

                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer1.token')
                ],
                'body' => [
                    'content' => 'Hello Appraiser! It\'s Customer 1',
                    'employee' => 'Customer 1'
                ]
            ]
        ];
    },

    'createMessage2:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/orders/'
                    .$capture->get('createOrder2.id').'/messages',

                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer2.token')
                ],
                'body' => [
                    'content' => 'Hello Appraiser! It\'s Customer 2',
                    'employee' => 'Customer 2'
                ]
            ]
        ];
    },

    'getMessages' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/messages',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'content' => 'Hello Appraiser! It\'s Customer 1'
                    ],
                ],
                'filter' => new ArrayFieldsFilter(['content'], true)
            ]
        ];
    },

    'getMessagesTotal' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/messages/total',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    'total' => 1,
                    'unread' => 1
                ]
            ]
        ];
    },

    'markAsReadMessage:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /appraisers/'.$runtime->getSession('appraiser')->get('user.id')
                    .'/messages/'.$runtime->getCapture()->get('createMessage1.id').'/mark-as-read',
            ]
        ];
    },

    'getMessageWithAllRead' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/messages/total',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    'total' => 1,
                    'unread' => 0
                ]
            ]
        ];
    },

    'getOrder1Totals' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/orders/totals',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    'paid' => [
                        'total' => 0,
                        'fee' => 0,
                        'techFee' => 0
                    ],
                    'unpaid' => [
                        'total' => 1,
                        'fee' => 10,
                        'techFee' => 20
                    ],
                ]
            ]
        ];
    },
    'getOrder2Totals' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer2.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/orders/totals',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer2.token')
                ]
            ],
            'response' => [
                'body' => [
                    'paid' => [
                        'total' => 1,
                        'fee' => 30,
                        'techFee' => 40
                    ],
                    'unpaid' => [
                        'total' => 0,
                        'fee' => 0,
                        'techFee' => 0
                    ],
                ]
            ]
        ];
    },
    'getAcceptedQueue' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/queues/accepted',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'id' => $runtime->getCapture()->get('createOrder1.id'),
                        'fileNumber' => $runtime->getCapture()->get('createOrder1.fileNumber')
                    ]
                ]
            ]
        ];
    },
    'getQueueCounters' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/queues/counters',

                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
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
    },
    'getCreditCard' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/payment/credit-card',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ]
        ];
    },

    'getSettings' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer1.id')
                    .'/appraisers/'.$runtime->getSession('appraiser')->get('user.id').'/settings',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinCustomer1.token')
                ]
            ],
            'response' => [
                'body' => [
                    'notifications' => [
                        [
                            'customer' => new Dynamic(function($v){ return is_array($v); }),
                            'email' => true
                        ]
                    ]
                ]
            ]
        ];
    }
];
