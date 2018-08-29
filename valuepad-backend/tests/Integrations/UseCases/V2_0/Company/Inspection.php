<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Log\Enums\Action;

$appraiser = uniqid('appraiser');
$manager = uniqid('manager');

$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

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
                    'taxId' => '09-4504401',
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
                    'taxId' => '09-4504401',
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


    'createOrder:init' => function(Runtime $runtime){

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        $data['jobType'] = 12;

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$runtime->getCapture()->get('createCompany.id').'/staff/'
                    .$runtime->getCapture()->get('getAppraiserStaff.0.id').'/orders',
                'auth' => 'customer',
                'includes' => ['property'],
                'body' => $data
            ]
        ];
    },

    'accept:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'POST /appraisers/'
                    .$runtime->getCapture()->get('createAppraiser.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/accept',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
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

    'scheduleInspection' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){

        return [
            'request' => [
                'url' => 'POST /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/schedule-inspection',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
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
                    'order' => $runtime->getCapture()->get('createOrder.id'),
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
                                return $data['id'] == $runtime->getCapture()->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::ACCEPTED,
                            'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                            'scheduledAt' => $scheduledAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ],
            'emails' => function(Runtime $runtime){
                return [
                    'body' => [
                       [
                           'from' => [
                               'no-reply@valuepad.com' => 'The ValuePad Team'
                           ],
                           'to' => [
                               $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.firstName')
                                   .' '.$runtime->getCapture()->get('createAppraiser.lastName'),
                           ],
                           'subject' => new Dynamic(function($value) use ($runtime){
                               return starts_with($value, 'Inspection Scheduled - Order on '.$runtime->getCapture()->get('createOrder.property.address1'));
                           }),
                           'contents' => new Dynamic(Dynamic::STRING)
                       ]
                    ]
                ];
            },

            'mobile' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){

                return [
                    'body' => [
                        [
                            'users' => [$runtime->getCapture()->get('createAppraiser.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'update-process-status'
                            ],
                            'message' => new Dynamic(function($value) use ($runtime){
                                return str_contains($value, ['"Inspection Scheduled"']);
                            }),
                            'extra' => [
                                'order' => $runtime->getCapture()->get('createOrder.id'),
                                'fileNumber' => $runtime->getCapture()->get('createOrder.fileNumber'),
                                'oldProcessStatus' => ProcessStatus::ACCEPTED,
                                'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED
                            ]
                        ]
                    ]
                ];
            },
        ];
    },
    'completeInspection' => function(Runtime $runtime) use ($estimatedCompletionDate, $completedAt){

        return [
            'request' => [
                'url' => 'POST /managers/'.$runtime->getCapture()->get('createManager.user.id').'/orders/'
                    .$runtime->getCapture()->get('createOrder.id').'/complete-inspection',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinManager.token')
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
                    'order' => $runtime->getCapture()->get('createOrder.id'),
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
                                return $data['id'] == $runtime->getCapture()->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                            'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                            'completedAt' => $completedAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ],
            'emails' => function(Runtime $runtime){
                return [
                    'body' => [
                        [
                            'from' => [
                                'no-reply@valuepad.com' => 'The ValuePad Team'
                            ],
                            'to' => [
                                $runtime->getCapture()->get('createAppraiser.email') => $runtime->getCapture()->get('createAppraiser.firstName')
                                    .' '.$runtime->getCapture()->get('createAppraiser.lastName'),
                            ],
                            'subject' => new Dynamic(function($value) use ($runtime){
                                return starts_with($value, 'Inspection Completed - Order on '.$runtime->getCapture()->get('createOrder.property.address1'));
                            }),
                            'contents' => new Dynamic(Dynamic::STRING)
                        ]
                    ]
                ];
            },

            'mobile' => function(Runtime $runtime) use ($completedAt, $estimatedCompletionDate){

                return [
                    'body' => [
                        [
                            'users' => [$runtime->getCapture()->get('createAppraiser.id')],
                            'notification' => [
                                'category' => 'order',
                                'name' => 'update-process-status'
                            ],
                            'message' => new Dynamic(function($value) use ($runtime){
                                return str_contains($value, ['"Inspection Completed"']);
                            }),
                            'extra' => [
                                'order' => $runtime->getCapture()->get('createOrder.id'),
                                'fileNumber' => $runtime->getCapture()->get('createOrder.fileNumber'),
                                'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                                'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED
                            ]
                        ]
                    ]
                ];
            },
        ];
    }
];
