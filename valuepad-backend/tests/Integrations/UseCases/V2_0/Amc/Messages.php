<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Entities\Message;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Response;
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

    'signinAmc:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $amc,
                'password' => 'password'
            ]
        ]
    ],


    'createOrder1:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/amcs/'
                    .$capture->get('createAmc.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    'createOrder2:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/amcs/'
                    .$capture->get('createAmc.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    'create1' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$capture->get('createAmc.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'content' => 'Hello AMC!'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'sender' => ['id' => $capture->get('createAmc.id')],
                    'content' => 'Hello AMC!',
                    'order' => $capture->get('createOrder1'),
                    'createdAt' => new Dynamic(Dynamic::DATETIME),
                    'isRead' => true,
                ],
                'filter' => new ItemFieldsFilter(['id', 'sender.id', 'content', 'order', 'createdAt', 'isRead'], true)
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'send-message',
                        'order' => $capture->get('createOrder1.id'),
                        'message' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    'channels' => ['private-user-'.$capture->get('createAmc.id')],
                    'event' => 'order:send-message',
                    'data' => [
                        'id' => new Dynamic(Dynamic::INT),
                        'sender' => ['id' => $capture->get('createAmc.id')],
                        'content' => 'Hello AMC!',
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

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id')
                    .'/messages/'.$capture->get('create1.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => $capture->get('create1')
            ]
        ];
    },

    'create2:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$capture->get('createAmc.id').'/orders/'
                    .$capture->get('createOrder2.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'content' => 'Hello AMC!'
                ]
            ]
        ];
    },
    'create3:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',

                'auth' => 'customer',

                'body' => [
                    'content' => 'Hello Test AMC!',
                    'employee' => 'John Black'
                ]
            ]
        ];
    },

    'create4:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder2.id').'/messages',

                'auth' => 'customer',

                'body' => [
                    'content' => 'Hello Test AMC!',
                    'employee' => 'John Black'
                ]
            ]
        ];
    },

    'get1Read' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'isRead' => true
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) use ($capture){
                        return $v['id'] == $capture->get('create1.id');
                    }),
                    new ItemFieldsFilter(['isRead'], true)
                ])
            ]
        ];
    },

    'get2Read' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'isRead' => true
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) use ($capture){
                        return $v['id'] == $capture->get('create2.id');
                    }),
                    new ItemFieldsFilter(['isRead'], true)
                ])
            ]
        ];
    },

    'get3Unread' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'isRead' => false
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) use ($capture){
                        return $v['id'] == $capture->get('create3.id');
                    }),
                    new ItemFieldsFilter(['isRead'], true)
                ])
            ]
        ];
    },

    'get4Unread' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'isRead' => false
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) use ($capture){
                        return $v['id'] == $capture->get('create4.id');
                    }),
                    new ItemFieldsFilter(['isRead'], true)
                ])
            ]
        ];
    },

    'getAllRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'parameters' => [
                    'filter' => [
                        'isRead' => 'true'
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
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
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'parameters' => [
                    'filter' => [
                        'isRead' => 'true'
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
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
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'total' => 4,
                    'unread' => 2
                ]
            ]
        ];
    },


    'markAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$capture->get('createAmc.id').'/messages/'
                    .$capture->get('create3.id').'/mark-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ]
        ];
    },

    'get3Read' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'isRead' => true
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) use ($capture){
                        return $v['id'] == $capture->get('create3.id');
                    }),
                    new ItemFieldsFilter(['isRead'], true)
                ])
            ]
        ];
    },

    'getTotal2' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'total' => 4,
                    'unread' => 1
                ]
            ]
        ];
    },

    'againMarkAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$capture->get('createAmc.id').'/messages/'
                    .$capture->get('create3.id').'/mark-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ]
        ];
    },

    'tryMarkAllAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$capture->get('createAmc.id').'/messages/mark-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],

                'body' => [
                    'messages' => [38888, 32313]
                ]
            ]
        ];
    },

    'markAllAsRead' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$capture->get('createAmc.id').'/messages/mark-as-read',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'messages' => [$capture->get('create4.id')]
                ]
            ]
        ];
    },

    'get4Read' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [
                    'isRead' => true
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) use ($capture){
                        return $v['id'] == $capture->get('create4.id');
                    }),
                    new ItemFieldsFilter(['isRead'], true)
                ])
            ]
        ];
    },

    'getTotal3' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages/total',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
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

    'updateCreatedAt:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'raw' => function(EntityManagerInterface $em) use ($capture){
                /**
                 * @var Message $m1
                 */
                $m1 = $em->find(Message::class, $capture->get('create1.id'));
                $m1->setCreatedAt(new DateTime('-10 days'));

                /**
                 * @var Message $m2
                 */
                $m2 = $em->find(Message::class, $capture->get('create2.id'));
                $m2->setCreatedAt(new DateTime('+10 days'));

                /**
                 * @var Message $m3
                 */
                $m3 = $em->find(Message::class, $capture->get('create3.id'));
                $m3->setCreatedAt(new DateTime('+40 days'));

                /**
                 * @var Message $m4
                 */
                $m4 = $em->find(Message::class, $capture->get('create4.id'));
                $m4->setCreatedAt(new DateTime('+50 days'));

                $em->flush();
            }
        ];
    },

    'getAllFromOrder1' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $m1 = $capture->get('create1');
        $m1['createdAt'] = new Dynamic(Dynamic::DATETIME);

        $m2 = $capture->get('create3');
        $m2['createdAt'] = new Dynamic(Dynamic::DATETIME);
        $m2['isRead'] = true;

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/orders/'
                    .$capture->get('createOrder1.id').'/messages',
                'parameters' => [
                    'orderBy' => 'createdAt:desc'
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [$m2,$m1]
            ]
        ];
    },

    'getAllFromOrder2' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $m1 = $capture->get('create2');
        $m1['createdAt'] = new Dynamic(Dynamic::DATETIME);

        $m2 = $capture->get('create4');
        $m2['createdAt'] = new Dynamic(Dynamic::DATETIME);
        $m2['isRead'] = true;

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/orders/'
                    .$capture->get('createOrder2.id').'/messages',
                'parameters' => [
                    'orderBy' => 'createdAt:desc'
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [$m2,$m1]
            ]
        ];
    },

    'getAll' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $m1 = $capture->get('create1');
        $m1['createdAt'] = new Dynamic(Dynamic::DATETIME);

        $m2 = $capture->get('create2');
        $m2['createdAt'] = new Dynamic(Dynamic::DATETIME);

        $m3 = $capture->get('create3');
        $m3['createdAt'] = new Dynamic(Dynamic::DATETIME);
        $m3['isRead'] = true;

        $m4 = $capture->get('create4');
        $m4['createdAt'] = new Dynamic(Dynamic::DATETIME);
        $m4['isRead'] = true;

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/messages',
                'parameters' => [
                    'orderBy' => 'createdAt:desc'
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ],
            ],
            'response' => [
                'body' => [$m4,$m3,$m2,$m1]
            ]
        ];
    },
];
