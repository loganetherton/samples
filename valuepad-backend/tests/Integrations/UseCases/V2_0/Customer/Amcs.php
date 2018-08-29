<?php
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\User\Enums\Status;

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

    'createAmc2:init' => [
        'request' => [
            'url' => 'POST /amcs',
            'auth' => 'guest',
            'body' => [
                'username' => uniqid('amc'),
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

    'approveAmc2:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc2.id'),
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

    'connect:init' => function(Runtime $runtime){
        return [
            'raw' => function(EntityManagerInterface $em) use ($runtime){
                /**
                 * @var Customer $customer
                 */
                $customer = $em->find(Customer::class, $runtime->getCapture()->get('createCustomer.id'));

                /**
                 * @var Amc $amc
                 */
                $amc = $em->find(Amc::class, $runtime->getCapture()->get('createAmc.id'));

                $customer->addAmc($amc);
                $em->flush();
            }
        ];
    },

    'getAll' => function(Runtime $runtime) use ($amc){
        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getCapture()->get('createCustomer.id').'/amcs',
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ],
            'response' => [
               'body' => [
                   [
                       'id' => $runtime->getCapture()->get('createAmc.id'),
                       'username' => $amc,
                       'email' => 'bestamc@ever.org',
                       'companyName' => 'Best AMC Ever!',
                       'displayName' => 'Best AMC Ever!',
                       'type' => 'amc'
                   ]
               ]
            ],
        ];
    },

    'getUnrelated' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /amcs/'.$runtime->getCapture()->get('createAmc2.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ],
            'response' => [
                'status' => 403
            ]
        ];
    },
    'getRelated' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /amcs/'.$runtime->getCapture()->get('createAmc.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ]
        ];
    },

    'assignOrder' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => $capture->get('addClient.id'),
            'clientDisplayedOnReport' => $capture->get('addClient.id')
        ]);

        $data['jobType'] = $capture->get('addJobType.id');

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/amcs/'.$capture->get('createAmc2.id').'/orders',
                'auth' => 'guest',
                'includes' => ['property', 'processStatus'],
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ],
                'body' => $data
            ],

            'live' => function(Runtime $runtime){
                $capture = $runtime->getCapture();
                $amc = $capture->get('createAmc2');
                return [
                    'body' => [
                        [
                            'channels' => ['private-user-'.$amc['id']],
                            'event' => 'order:create-log',
                            'data' => new Dynamic(function($data){
                                return $data['action'] === Action::CREATE_ORDER;
                            })
                        ],
                        [
                            'channels' => ['private-user-'.$amc['id']],
                            'event' => 'order:create',
                            'data' => [
                                'id' => $capture->get('assignOrder.id'),
                                'fileNumber' => $capture->get('assignOrder.fileNumber'),
                                'processStatus' => $capture->get('assignOrder.processStatus')
                            ]
                        ],
                    ]
                ];
            },

            'emails' => function(Runtime $runtime){
                $capture = $runtime->getCapture();

                $amc = $capture->get('createAmc2');

                return  [
                    'body' => [
                        [
                            'from' => [
                                'no-reply@valuepad.com' => 'The ValuePad Team'
                            ],
                            'to' => [
                                $amc['email'] => $amc['displayName'],
                            ],
                            'subject' => new Dynamic(function($value) use ($capture){
                                return starts_with($value, 'New - Order on '.$capture->get('assignOrder.property.address1'));
                            }),
                            'contents' => new Dynamic(Dynamic::STRING)
                        ]
                    ]
                ];
            },

            'mobile' => function(Runtime $runtime){
                $capture = $runtime->getCapture();
                $amc = $capture->get('createAmc2');

                return [
                    'body' => [
                        [
                            'users' => [$amc['id']],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'create'
                            ],
                            'message' => new Dynamic(function($value) use ($capture){
                                $property = $capture->get('assignOrder.property');

                                return str_contains($value, $property['address1'].', '.$property['city'].', '.$property['state']['code'].' '.$property['zip']);
                            }),
                            'extra' => [
                                'order' => $capture->get('assignOrder.id'),
                                'fileNumber' => $capture->get('assignOrder.fileNumber'),
                                'processStatus' => ProcessStatus::FRESH
                            ]
                        ]
                    ]
                ];
            }
        ];
    },

    'getRelated2' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /amcs/'.$runtime->getCapture()->get('createAmc2.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $runtime->getCapture()->get('signinCustomer.token')
                ]
            ]
        ];
    }
];