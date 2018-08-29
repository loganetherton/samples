<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use Ascope\QA\Support\Filters\FirstFilter;

$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

return [
    'defineFormats:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/settings/documents/formats',
                'auth' => 'customer',
                'body' => [
                    'jobType' => 7,
                    'primary' => ['pdf'],
                    'extra' => ['zoo']
                ]
            ]
        ];
    },
    'createOrder:init' => function(Runtime $runtime) use ($dueDate){
        $customerSession = $runtime->getSession('customer');
        $amcSession = $runtime->getSession('amc');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);
        $data['jobType'] = 7;

        $data['techFee'] = 9.99;

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customerSession->get('user.id').'/amcs/'
                    .$amcSession->get('user.id').'/orders',
                'auth' => 'customer',
                'body' => $data
            ]
        ];
    },
    'accept:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'POST /amcs/'
                    .$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/accept',
                'auth' => 'amc'
            ]
        ];
    },
    'scheduleInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/schedule-inspection',
                'auth' => 'amc',
                'body' => [
                    'scheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ]
        ];
    },
    'completeInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $completedAt){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/complete-inspection',
                'auth' => 'amc',
                'body' => [
                    'completedAt' => $completedAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
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
    'create2ndPdf:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],
    'createZoo:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.zoo'
            ]
        ]
    ],
    'create2ndZoo:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.zoo'
            ]
        ]
    ],
    'createAci:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.aci'
            ]
        ]
    ],
    'createXml:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.xml'
            ]
        ]
    ],
    'createZap:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.zap'
            ]
        ]
    ],

    'complete' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/document',
                'auth' => 'amc',
                'body' => [
                    'primary' => [
                        'id' => $capture->get('createPdf.id'),
                        'token' => $capture->get('createPdf.token')
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'primary' => $capture->get('createPdf'),
                    'primaries' => [$capture->get('createPdf')],
                    'createdAt' => new Dynamic(Dynamic::DATETIME),
                    'extra' => [],
                    'showToAppraiser' => true
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'create-document',
                        'order' => $capture->get('createOrder.id'),
                        'document' => new Dynamic(Dynamic::INT)
                    ],
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                        'newProcessStatus' => ProcessStatus::READY_FOR_REVIEW
                    ]
                ]
            ],
            'emails' => [
                'body' => []
            ],
            'mobile' => [
                'body' => []
            ],
            'live' => [
                'body' => [
                    'event' => 'order:create-document',
                    'channels' => ['private-user-'.$runtime->getSession('amc')->get('user.id')],
                    'data' => [
                        'order' => [
                            'id' => $capture->get('createOrder.id'),
                            'fileNumber' => $capture->get('createOrder.fileNumber')
                        ],
                        'document' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'primary' => $capture->get('createPdf'),
                            'primaries' => [$capture->get('createPdf')],
                            'createdAt' => new Dynamic(Dynamic::DATETIME),
                            'extra' => [],
                            'showToAppraiser' => true
                        ]
                    ]
                ],
                'filter' => new FirstFilter(function($k, $v){
                    return $v['event'] == 'order:create-document';
                })
            ]
        ];
    },

    'getCompletedOrder' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'amc',
                'includes' => ['processStatus']
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'processStatus' => 'ready-for-review'
                ]
            ]
        ];
    },

    'getDocument' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/document',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => $capture->get('complete')
            ]
        ];
    },

    'addZooExtra' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/document',
                'auth' => 'amc',
                'body' => [
                    'extra' => [
                        [
                            'id' => $capture->get('createZoo.id'),
                            'token' => $capture->get('createZoo.token')
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-document',
                        'order' => $capture->get('createOrder.id'),
                        'document' => $capture->get('complete.id')
                    ],
                ]
            ],

            'mobile' => [
                'body' => []
            ],

            'email' => [
                'body' => []
            ],

            'live' => [
                'body' => [
                    'event' => 'order:update-document',
                    'channels' => ['private-user-'.$runtime->getSession('amc')->get('user.id')],
                    'data' => [
                        'order' => [
                            'id' => $capture->get('createOrder.id'),
                            'fileNumber' => $capture->get('createOrder.fileNumber')
                        ],
                        'document' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'primary' => $capture->get('createPdf'),
                            'primaries' => [$capture->get('createPdf')],
                            'createdAt' => new Dynamic(Dynamic::DATETIME),
                            'extra' => new Dynamic(function($value){
                                return !!$value;
                            }),
                            'showToAppraiser' => true
                        ]
                    ]
                ],
                'filter' => new FirstFilter(function($k, $v){
                    return $v['event'] == 'order:update-document';
                })
            ]
        ];
    },

    'getDocumentWithZooExtra' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/document',
                'auth' => 'amc'
            ],
            'response' => [
                'body' => [
                    'extra' => [
                        $capture->get('createZoo')
                    ]
                ],
                'filter' => new ItemFieldsFilter(['extra'], true)
            ]
        ];
    },
    'formats1' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/document/formats',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => [
                    'primary' => ['pdf'],
                    'extra' => ['zoo']
                ]
            ]
        ];
    },
];
