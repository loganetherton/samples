<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$appraiser = uniqid('appraiser');
$appraiser2 = uniqid('appraiser2');
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
                    'taxId' => '09-4504567',
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
                    'taxId' => '09-4504567',
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
                'includes' => ['property'],
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

    'accept' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder1.id').'/accept',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'invitation',
                        'event' => 'accept',
                        'invitation' => new Dynamic(Dynamic::INT),
                        'appraiser' => $runtime->getCapture()->get('createAppraiser.id')
                    ],
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $runtime->getCapture()->get('createOrder1.id'),
                        'oldProcessStatus' => ProcessStatus::FRESH,
                        'newProcessStatus' => ProcessStatus::ACCEPTED
                    ]
                ],
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getCapture()->get('createManager.user.id'),
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getCapture()->get('createManager.user.id'),
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($runtime){
                                return $data['id'] == $runtime->getCapture()->get('createOrder1.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::FRESH,
                            'newProcessStatus' => ProcessStatus::ACCEPTED,
                        ]
                    ]
                ]
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.displayName'),
                        ],
                        'subject' => new Dynamic(function($value) use ($runtime){
                            return starts_with($value, 'Accepted - Order on '.$runtime->getCapture()->get('createOrder1.property.address1'));
                        }),
                        'contents' => new Dynamic(function($value){
                            return str_contains($value, 'Man Ager');
                        })
                    ]
                ]
            ],
            'mobile' => function(Runtime $runtime){
                return [
                    'body' => [
                        [
                            'users' => [$runtime->getCapture()->get('createAppraiser.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'update-process-status'
                            ],
                            'message' => new Dynamic(function($value){
                                return str_contains($value, 'Man Ager');
                            }),
                            'extra' => [
                                'order' => $runtime->getCapture()->get('createOrder1.id'),
                                'fileNumber' => $runtime->getCapture()->get('createOrder1.fileNumber'),
                                'oldProcessStatus' => ProcessStatus::FRESH,
                                'newProcessStatus' => ProcessStatus::ACCEPTED,
                            ]
                        ]
                    ]
                ];
            }
        ];
    },

    'getAllOrders' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
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
    'getOneOrder' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder1.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'id' => $runtime->getCapture()->get('createOrder1.id'),
                    'fileNumber' => $runtime->getCapture()->get('createOrder1.fileNumber')
                ]
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
                'includes' => ['property'],
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    'acceptWithConditions' => function(Runtime $runtime){
        $dueDate = (new DateTime('+2 years'));

        return [
            'request' => [
                'url' => 'POST /managers/'
                    .$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder2.id').'/accept-with-conditions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'request' => 'fee-increase-and-due-date-extension',
                    'fee' => 100.01,
                    'dueDate' => $dueDate->format(DateTime::ATOM),
                    'explanation' => 'The project is too large.'
                ],
            ],
            'push' => [
                'body' => [
                    'type' => 'order',
                    'event' => 'accept-with-conditions',
                    'order' => $runtime->getCapture()->get('createOrder2.id'),
                    'conditions' => [
                        'request' => 'fee-increase-and-due-date-extension',
                        'fee' => 100.01,
                        'dueDate' => $dueDate->format(DateTime::ATOM),
                        'explanation' => 'The project is too large.'
                    ]
                ],
                'single' => true
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.displayName'),
                        ],
                        'subject' => new Dynamic(function($value) use ($runtime){
                            return starts_with($value, 'Accepted With Conditions - Order on '.$runtime->getCapture()->get('createOrder2.property.address1'));
                        }),
                        'contents' => new Dynamic(function($value) use ($dueDate){
                            return str_contains($value, [
                                'Reason:</b> Fee increase and due date extension',
                                'Total Requested Fee:</b> 100.01',
                                'Explanation:</b> The project is too large',
                                'Man Ager </b> has accepted',
                                'Due Date:</b> '.$dueDate->format('m/d/Y')
                            ]);
                        })
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getCapture()->get('createManager.user.id'),
                        ],
                        'event' => 'order:accept-with-conditions',
                        'data' => new Dynamic(function($data){
                            return is_array($data);
                        }),
                    ],
                ]
            ],
            'mobile' => function(Runtime $runtime) use ($dueDate){
                return [
                    'body' => [
                        [
                            'users' => [$runtime->getCapture()->get('createAppraiser.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'accept-with-conditions'
                            ],
                            'message' => new Dynamic(function($value){
                                return str_contains($value, 'Man Ager');
                            }),
                            'extra' => [
                                'order' => $runtime->getCapture()->get('createOrder2.id'),
                                'conditions' => [
                                    'request' => 'fee-increase-and-due-date-extension',
                                    'fee' => 100.01,
                                    'dueDate' => $dueDate->format(DateTime::ATOM),
                                    'explanation' => 'The project is too large.'
                                ]
                            ]
                        ]
                    ]
                ];
            }
        ];
    },
    'createOrder3:init' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$runtime->getCapture()->get('createCompany.id').'/staff/'
                    .$runtime->getCapture()->get('getAppraiserStaff.0.id').'/orders',
                'auth' => 'customer',
                'includes' => ['property'],
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    'decline' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /managers/'
                    .$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder3.id').'/decline',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
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
                    'order' => $runtime->getCapture()->get('createOrder3.id'),
                    'reason' => 'other',
                    'message' => 'some message'
                ],
                'single' => true
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.displayName'),
                        ],
                        'subject' => new Dynamic(function($value) use ($runtime){
                            return starts_with($value, 'Declined - Order on '.$runtime->getCapture()->get('createOrder3.property.address1'));
                        }),
                        'contents' => new Dynamic(function($value){
                            return str_contains($value, [
                                'Reason:</b> Other',
                                'Explanation:</b> some message',
                                'Man Ager </b> has declined',
                            ]);
                        })
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getCapture()->get('createManager.user.id'),
                        ],
                        'event' => 'order:decline',
                        'data' => new Dynamic(function($data){
                            return is_array($data);
                        }),
                    ],
                ]
            ],
            'mobile' => function(Runtime $runtime){
                return [
                    'body' => [
                        [
                            'users' => [$runtime->getCapture()->get('createAppraiser.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'decline'
                            ],
                            'message' => new Dynamic(function($value){
                                return str_contains($value, 'Man Ager');
                            }),
                            'extra' => [
                                'order' => $runtime->getCapture()->get('createOrder3.id'),
                                'reason' => 'other',
                                'message' => 'some message'
                            ]
                        ]
                    ]
                ];
            }
        ];
    },

    'createBidRequest:init' => function(Runtime $runtime){

        $requestBody = OrdersFixture::getAsBidRequest($runtime->getHelper(), ['client' => 1, 'clientDisplayedOnReport' => 2]);

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$runtime->getCapture()->get('createCompany.id').'/staff/'
                    .$runtime->getCapture()->get('getAppraiserStaff.0.id').'/orders',
                'auth' => 'customer',
                'includes' => ['property'],
                'body' => $requestBody
            ]
        ];
    },

    'submitBid' => function(Runtime $runtime){

        $ecd = (new DateTime('+1 month'));

        return [
            'request' => [
                'url' => 'POST /managers/'
                    .$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createBidRequest.id').'/bid',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'amount' => 1000,
                    'estimatedCompletionDate' => $ecd->format(DateTime::ATOM),
                    'comments' => 'Some comments'
                ]
            ],
            'response' => [
                'body' => [
                    'amount' => 1000,
                    'estimatedCompletionDate' => $ecd->format(DateTime::ATOM),
                    'comments' => 'Some comments'
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'submit-bid',
                        'order' => $runtime->getCapture()->get('createBidRequest.id')
                    ]
                ]
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'no-reply@valuepad.com' => 'The ValuePad Team'
                        ],
                        'to' => [
                            $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.displayName'),
                        ],
                        'subject' => new Dynamic(function($value) use ($runtime){
                            return starts_with($value, 'Bid Submitted - Order on '.$runtime->getCapture()->get('createBidRequest.property.address1'));
                        }),
                        'contents' => new Dynamic(function($value) use ($ecd){
                            return str_contains($value, [
                                'Amount:</b> 1000',
                                'Estimated Completion Date:</b> '.$ecd->format('m/d/Y'),
                                'Comments: </b> Some comments',
                            ]);
                        })
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getCapture()->get('createAppraiser.id'),
                            'private-user-'.$runtime->getCapture()->get('createManager.user.id'),
                        ],
                        'event' => 'order:submit-bid',
                        'data' => [
                            'order' => [
                                'id' => $runtime->getCapture()->get('createBidRequest.id'),
                                'fileNumber' => $runtime->getCapture()->get('createBidRequest.fileNumber')
                            ],
                            'bid' => [
                                'amount' => 1000,
                                'estimatedCompletionDate' => $ecd->format(DateTime::ATOM),
                                'comments' => 'Some comments'
                            ]
                        ],
                    ],
                ]
            ],
            'mobile' => function(Runtime $runtime) use ($ecd){
                return [
                    'body' => [
                        [
                            'users' => [$runtime->getCapture()->get('createAppraiser.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'submit-bid'
                            ],
                            'message' => new Dynamic(function($value){
                                return str_contains($value, 'Man Ager has submitted');
                            }),
                            'extra' => [
                                'order' => $runtime->getCapture()->get('createBidRequest.id'),
                                'bid' => [
                                    'amount' => 1000,
                                    'estimatedCompletionDate' => $ecd->format(DateTime::ATOM),
                                    'comments' => 'Some comments'
                                ]
                            ]
                        ]
                    ]
                ];
            }
        ];
    },

    'getQueues' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id').'/queues/accepted',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
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
    'getCounters' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id').'/queues/counters',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'new' => 1,
                    'accepted' => 1,
                    'inspected' => 0,
                    'scheduled' => 0,
                    'onHold' => 0,
                    'late' => 0,
                    'readyForReview' => 0,
                    'completed' => 0,
                    'revision' => 0,
                    'due' => 1,
                    'open' => 2,
                    'all' => 2
                ]
            ]
        ];
    },

    'createAppraiser2:init' => function(Runtime $runtime) use ($appraiser2){

        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser2,
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

    'addAppraiser2ToCompany:init' => function(Runtime $runtime){
        return [
            'raw' => function(EntityManagerInterface $entityManager) use ($runtime){

                $appraiser = $entityManager->getReference(Appraiser::class, $runtime->getCapture()->get('createAppraiser2.id'));
                $company = $entityManager->getReference(Company::class, $runtime->getCapture()->get('createCompany.id'));
                $branch = $entityManager->getReference(Branch::class, $runtime->getCapture()->get('createBranch.id'));

                $staff = new Staff();

                $staff->setCompany($company);
                $staff->setBranch($branch);
                $staff->setUser($appraiser);

                $entityManager->persist($staff);
                $entityManager->flush();
            }
        ];
    },

    'signinAppraiser2:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $appraiser2,
                'password' => 'password'
            ]
        ]
    ],

    'getOrdersOfAppraiser2WhenEmpty' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getCapture()->get('createAppraiser2.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser2.token')
                ],
            ],
            'response' => [
                'body' => []
            ]
        ];
    },

    'reassign' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder1.id').'/reassign',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ],
                'body' => [
                    'appraiser' => $runtime->getCapture()->get('createAppraiser2.id')
                ]
            ],
        ];
    },

    'getOrdersOfAppraiser2' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getCapture()->get('createAppraiser2.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser2.token')
                ],
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

    'getAppraiserStaff2:init' => function (Runtime $runtime) {
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

    'promoteAppraiserToManager:init' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'PATCH /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('getAppraiserStaff.0.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'isManager' => true
                ]
            ]
        ];
    },

    'addPermissionsToFirstAppraiser:init' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('getAppraiserStaff.0.id').'/permissions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'data' => [
                        $runtime->getCapture()->get('getAppraiserStaff2.2.id')
                    ]
                ]
            ]
        ];
    },

    'createOrder4:init' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getSession('customer')->get('user.id').'/appraisers/'
                    .$runtime->getCapture()->get('createAppraiser.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    // There was a bug where if you're an appraiser that manages other appraisers,
    // certain order endpoints would result in 404 due to some error in the query constraint.
    'getAppraiserOrderWhenManagingAppraiser2' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getCapture()->get('createAppraiser.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder4.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $runtime->getCapture()->get('createOrder4.id')
                ],
                'filter' => new ItemFieldsFilter(['id'], true)
            ]
        ];
    },

    'getAppraiserAndSubordinatesOrders' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getCapture()->get('createAppraiser.id').'/orders/',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'body' => [
                    ['id' => $runtime->getCapture()->get('createOrder1.id'), 'fileNumber' => $runtime->getCapture()->get('createOrder1.fileNumber')],
                    ['id' => $runtime->getCapture()->get('createBidRequest.id'), 'fileNumber' => $runtime->getCapture()->get('createBidRequest.fileNumber')],
                    ['id' => $runtime->getCapture()->get('createOrder4.id'), 'fileNumber' => $runtime->getCapture()->get('createOrder4.fileNumber')],
                ]
            ]
        ];
    },

    'getCompanyOrdersForAccounting' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getCapture()->get('createAppraiser.id').'/orders/accounting',
                'parameters' => [
                    'filter' => [
                        'company' => $runtime->getCapture()->get('createCompany.id')
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token'),
                ]
            ],
            'response' => [
                'body' => [
                    ['id' => $runtime->getCapture()->get('createOrder1.id'), 'fileNumber' => $runtime->getCapture()->get('createOrder1.fileNumber')],
                    ['id' => $runtime->getCapture()->get('createBidRequest.id'), 'fileNumber' => $runtime->getCapture()->get('createBidRequest.fileNumber')],
                ]
            ]
        ];
    },

    'getAppraiserOrdersForAccounting' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getCapture()->get('createAppraiser.id').'/orders/accounting',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token'),
                ]
            ],
            'response' => [
                'body' => [
                    ['id' => $runtime->getCapture()->get('createOrder4.id'), 'fileNumber' => $runtime->getCapture()->get('createOrder4.fileNumber')],
                ]
            ]
        ];
    },

    'getManagerOrdersForAccounting' => function (Runtime $runtime) {
        return [
            'request' => [
                'url' => 'GET /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders/accounting',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    ['id' => $runtime->getCapture()->get('createOrder1.id'), 'fileNumber' => $runtime->getCapture()->get('createOrder1.fileNumber')],
                    ['id' => $runtime->getCapture()->get('createBidRequest.id'), 'fileNumber' => $runtime->getCapture()->get('createBidRequest.fileNumber')],
                ]
            ]
        ];
    },
    // Create an order to be accepted by appraiser1
    'createOrder5:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$runtime->getCapture()->get('createCompany.id').'/staff/'
                    .$runtime->getCapture()->get('getAppraiserStaff.0.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 3,
                    'clientDisplayedOnReport' => 2
                ])
            ],
        ];
    },
    // Accept order so it can be completed
    'acceptOrder5' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('signinAppraiser.user.id').'/orders/'
                    .$capture->get('createOrder5.id').'/accept',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
            ]
        ];
    },
    // Create a PDF for uploading documents
    'createPdf:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],
    // Complete the order to check for tinAtCompletion
    'completeOrder5' => function(Runtime $runtime){
        $session = $runtime->getSession('appraiser');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('signinAppraiser.user.id').'/orders/'.$capture->get('createOrder5.id').'/document',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'primary' => [
                        'id' => $capture->get('createPdf.id'),
                        'token' => $capture->get('createPdf.token')
                    ]
                ]
            ]
        ];
    },
    // Check tinAtCompletion
    'getOrderToCheckTinAtCompletion' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        return [
            'request' => [
                'url' => 'GET /appraisers/'.$capture->get('signinAppraiser.user.id').'/orders/'.$capture->get('createOrder5.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'tinAtCompletion' => '555-32-3322'
                ],
                'filter' => new ItemFieldsFilter(['tinAtCompletion'], true)
            ]
        ];
    },
];
