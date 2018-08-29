<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Core\User\Enums\Status;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Log\Enums\Action;

$amc = uniqid('amc');
$customer = uniqid('customer');

$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

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

    'updateSettings1:init' => function(Runtime $runtime){
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

    'createOrder3:init' => function(Runtime $runtime){

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

    'getOne' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/orders/'.$capture->get('createOrder2.id'),
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => $capture->get('createOrder2')
            ]
        ];
    },

    'getAll' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'total' => 3
            ]
        ];
    },

    'getTotals' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/orders/totals',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'paid' => [
                        'total' => new Dynamic(Dynamic::FLOAT),
                        'fee' => new Dynamic(Dynamic::FLOAT),
                        'techFee' => new Dynamic(Dynamic::FLOAT)
                    ],
                    'unpaid' => [
                        'total' => new Dynamic(Dynamic::FLOAT),
                        'fee' => new Dynamic(Dynamic::FLOAT),
                        'techFee' => new Dynamic(Dynamic::FLOAT)
                    ],
                ]
            ]
        ];
    },

    'getAllAdditionalStatuses' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder1');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/orders/'.$order['id'].'/additional-statuses',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
            ],
            'response' => [
                'body' => [$runtime->getCapture()->get('createAdditionalStatus')]
            ]
        ];
    },

    'changeAdditionalStatus' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder1');

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/orders/'
                    .$order['id'].'/change-additional-status',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'additionalStatus' => $runtime->getCapture()->get('createAdditionalStatus.id'),
                    'comment' => 'Test from AMC'
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'change-additional-status',
                        'order' => $order['id'],
                        'oldAdditionalStatus' => null,
                        'oldAdditionalStatusComment' => null,
                        'newAdditionalStatus' => $runtime->getCapture()->get('createAdditionalStatus.id'),
                        'newAdditionalStatusComment' => 'Test from AMC'
                    ]
                ]
            ],
            'emails' => [
                'body' => []
            ],
            'mobile' => [
                'body' => []
            ]
        ];
    },

    'getOneWithAdditionalStatus' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/orders/'.$capture->get('createOrder1.id'),
                'auth' => 'guest',
                'includes' => ['additionalStatus', 'additionalStatusComment'],
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder1.id'),
                    'fileNumber' => $capture->get('createOrder1.fileNumber'),
                    'additionalStatus' => $capture->get('createAdditionalStatus'),
                    'additionalStatusComment' => 'Test from AMC'
                ]
            ]
        ];
    },

    'acceptWithConditions' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder1');

        $dueDate = (new DateTime('+2 years'))->format(DateTime::ATOM);

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/orders/'.$order['id'].'/accept-with-conditions',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'request' => 'fee-increase-and-due-date-extension',
                    'fee' => 100.01,
                    'dueDate' => $dueDate,
                    'explanation' => 'The project is too large.'
                ],
            ],
            'push' => [
                'body' => [
                    'type' => 'order',
                    'event' => 'accept-with-conditions',
                    'order' => $order['id'],
                    'conditions' => [
                        'request' => 'fee-increase-and-due-date-extension',
                        'fee' => 100.01,
                        'dueDate' => $dueDate,
                        'explanation' => 'The project is too large.'
                    ]
                ],
                'single' => true
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:accept-with-conditions',
                        'data' => new Dynamic(function($data){
                            return is_array($data);
                        }),
                    ],
                ]
            ]
        ];
    },

    'getAllAfterAcceptWithConditions' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'total' => 2
            ]
        ];
    },

    'decline' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder2');

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/orders/'
                    .$order['id'].'/decline',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'reason' => 'other',
                    'message' => 'some message'
                ]
            ],
            'push' => [
                'body' => [
                    'type' => 'order',
                    'event' => 'decline',
                    'order' => $order['id'],
                    'reason' => 'other',
                    'message' => 'some message'
                ],
                'single' => true
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:decline',
                        'data' => new Dynamic(function($data){
                            return is_array($data);
                        }),
                    ],
                ]
            ]
        ];
    },

    'getAllAfterDecline' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$capture->get('createAmc.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinAmc.token')
                ]
            ],
            'response' => [
                'total' => 1
            ]
        ];
    },

    'accept' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder3');

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/orders/'.$order['id'].'/accept',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'push' => [
                'body' => [
                    'type' => 'order',
                    'event' => 'update-process-status',
                    'order' => $order['id'],
                    'oldProcessStatus' => ProcessStatus::FRESH,
                    'newProcessStatus' => ProcessStatus::ACCEPTED
                ],
                'single' => true
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($order){
                                return $data['id'] == $order['id'];
                            }),
                            'oldProcessStatus' => ProcessStatus::FRESH,
                            'newProcessStatus' => ProcessStatus::ACCEPTED,
                        ]
                    ]
                ]
            ],
            'emails' => [
                'body' => []
            ],
            'mobile' => [
                'body' => []
            ]
        ];
    },

    'getAccepted' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder3');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/orders/'.$order['id'],
                'includes' => ['processStatus', 'acceptedAt'],
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'fileNumber' => new Dynamic(Dynamic::STRING),
                    'processStatus' => ProcessStatus::ACCEPTED,
                    'acceptedAt' => new Dynamic(Dynamic::DATETIME)
                ]
            ]
        ];
    },

    'scheduleInspection' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder3');

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/orders/'
                    .$order['id'].'/schedule-inspection',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'scheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ],
            'push' => [
                'body' => [
                    'type' => 'order',
                    'event' => 'update-process-status',
                    'order' => $order['id'],
                    'scheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'oldProcessStatus' => ProcessStatus::ACCEPTED,
                    'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED
                ],
                'single' => true
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($order){
                                return $data['id'] == $order['id'];
                            }),
                            'oldProcessStatus' => ProcessStatus::ACCEPTED,
                            'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                            'scheduledAt' => $scheduledAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ],
            'emails' => [
                'body' => []
            ],

            'mobile' => [
                'body' => []
            ]
        ];
    },

    'getScheduled' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder3');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/orders/'.$order['id'],
                'includes' => [
                    'inspectionScheduledAt',
                    'inspectionCompletedAt',
                    'estimatedCompletionDate',
                    'processStatus'
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'fileNumber' => new Dynamic(Dynamic::STRING),
                    'processStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                    'inspectionScheduledAt' => $scheduledAt,
                    'inspectionCompletedAt' => null,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                ]
            ]
        ];
    },

    'completeInspection' => function(Runtime $runtime) use ($estimatedCompletionDate, $completedAt){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder3');

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/orders/'
                    .$order['id'].'/complete-inspection',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'completedAt' => $completedAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ],
            'push' => [
                'body' => [
                    'type' => 'order',
                    'event' => 'update-process-status',
                    'order' => $order['id'],
                    'completedAt' => $completedAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                    'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED
                ],
                'single' => true
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
                    [
                        'channels' => ['private-user-'.$amc['id']],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($order){
                                return $data['id'] == $order['id'];
                            }),
                            'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                            'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                            'completedAt' => $completedAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ],
            'emails' => [
                'body' => []
            ],

            'mobile' => [
                'body' => []
            ]
        ];
    },

    'getCompleted' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt, $completedAt){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder3');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/orders/'.$order['id'],
                'includes' => [
                    'inspectionScheduledAt',
                    'inspectionCompletedAt',
                    'estimatedCompletionDate',
                    'processStatus'
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'fileNumber' => new Dynamic(Dynamic::STRING),
                    'processStatus' => ProcessStatus::INSPECTION_COMPLETED,
                    'inspectionScheduledAt' => $scheduledAt,
                    'inspectionCompletedAt' => $completedAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                ]
            ]
        ];
    },

    'delete' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder3');

        return [
            'request' => [
                'url' => 'DELETE /amcs/'.$amc['id'].'/orders/'.$order['id'],
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
];
