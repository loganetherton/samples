<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
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
                    'taxId' => '09-4504500',
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
                    'taxId' => '09-4504500',
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

    'signinManager:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $manager,
                'password' => 'secret'
            ]
        ]
    ],

    'createOrder' => function(Runtime $runtime){
        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$runtime->getCapture()->get('createCompany.id').'/staff/'
                    .$runtime->getCapture()->get('getAppraiserStaff.0.id').'/orders',
                'includes' => ['property', 'customer'],
                'auth' => 'customer',
                'body' => $data
            ],
            'live' => function(Runtime $runtime){
                $customerSession = $runtime->getSession('customer');

                $capture = $runtime->getCapture();
                $order = $capture->get('createOrder');

                return [
                    'body' => [
                        'channels' => [
                            'private-user-'.$capture->get('createAppraiser.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$capture->get('createAppraiser.id'),
                            'private-user-'.$capture->get('createManager.user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'action' => Action::CREATE_ORDER,
                            'actionLabel' => 'New Order',
                            'message' => sprintf(
                                'You have received a new order on %s, %s, %s %s from %s.',
                                $order['property']['address1'],
                                $order['property']['city'],
                                $order['property']['state']['code'],
                                $order['property']['zip'],
                                $order['customer']['name']

                            ),
                            'user' => new Dynamic(function($data) use ($customerSession){
                                return $data['id'] == $customerSession->get('user.id') && $data['type'] == 'customer';
                            }),
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'extra' => [
                                'user' => $customerSession->get('user.name'),
                                'customer' => $order['customer']['name'],
                                'address1' => $capture->get('createOrder.property.address1'),
                                'address2' => $capture->get('createOrder.property.address2'),
                                'city' => $capture->get('createOrder.property.city'),
                                'zip' => $capture->get('createOrder.property.zip'),
                                'state' => $capture->get('createOrder.property.state'),
                            ],
                            'createdAt' => new Dynamic(Dynamic::DATETIME)
                        ],
                    ],
                    'filter' => new FirstFilter(function($k, $data){
                        return $data['event'] === 'order:create-log';
                    })
                ];
            }
        ];
    },

    'getAllByManager' => function(Runtime $runtime){
        $customerSession = $runtime->getSession('customer');

        $capture = $runtime->getCapture();

        $order = $capture->get('createOrder');

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/logs',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ],
                'parameters' => [
                    'perPage' => 1000
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'action' => Action::CREATE_ORDER,
                    'actionLabel' => 'New Order',
                    'message' => sprintf(
                        'You have received a new order on %s, %s, %s %s from %s.',
                        $order['property']['address1'],
                        $order['property']['city'],
                        $order['property']['state']['code'],
                        $order['property']['zip'],
                        $order['customer']['name']

                    ),
                    'user' => new Dynamic(function($data) use ($customerSession){
                        return $data['id'] == $customerSession->get('user.id') && $data['type'] == 'customer';
                    }),
                    'order' => new Dynamic(function($data) use ($capture){
                        return $data['id'] == $capture->get('createOrder.id');
                    }),
                    'extra' => [
                        'user' => $customerSession->get('user.name'),
                        'customer' => $order['customer']['name'],
                        'address1' => $capture->get('createOrder.property.address1'),
                        'address2' => $capture->get('createOrder.property.address2'),
                        'city' => $capture->get('createOrder.property.city'),
                        'zip' => $capture->get('createOrder.property.zip'),
                        'state' => $capture->get('createOrder.property.state'),
                    ],
                    'createdAt' => new Dynamic(Dynamic::DATETIME)
                ],
                'filter' => new FirstFilter(function($k, $v) use ($capture){
                    return $v['order']['id'] == $capture->get('createOrder.id');
                }),
            ]
        ];
    },

    'getAllByOrder' => function(Runtime $runtime){
        $customerSession = $runtime->getSession('customer');

        $capture = $runtime->getCapture();
        $order = $capture->get('createOrder');

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/orders/'.$order['id'].'/logs',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'action' => Action::CREATE_ORDER,
                        'actionLabel' => 'New Order',
                        'message' => sprintf(
                            'You have received a new order on %s, %s, %s %s from %s.',
                            $order['property']['address1'],
                            $order['property']['city'],
                            $order['property']['state']['code'],
                            $order['property']['zip'],
                            $order['customer']['name']

                        ),
                        'user' => new Dynamic(function($data) use ($customerSession){
                            return $data['id'] == $customerSession->get('user.id') && $data['type'] == 'customer';
                        }),
                        'order' => new Dynamic(function($data) use ($capture){
                            return $data['id'] == $capture->get('createOrder.id');
                        }),
                        'extra' => [
                            'user' => $customerSession->get('user.name'),
                            'customer' => $order['customer']['name'],
                            'address1' => $capture->get('createOrder.property.address1'),
                            'address2' => $capture->get('createOrder.property.address2'),
                            'city' => $capture->get('createOrder.property.city'),
                            'zip' => $capture->get('createOrder.property.zip'),
                            'state' => $capture->get('createOrder.property.state'),
                        ],
                        'createdAt' => new Dynamic(Dynamic::DATETIME)
                    ]
                ]
            ]
        ];
    }
];
