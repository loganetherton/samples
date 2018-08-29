<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\LastFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Services\WorkflowService;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Tests\Integrations\Support\Filters\MessageAndExtraFilter;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

return [
    'createOrder:init' => function(Runtime $runtime) use ($dueDate){
        $customerSession = $runtime->getSession('customer');
        $amcSession = $runtime->getSession('amc');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);
        $data['dueDate'] = $dueDate;

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customerSession->get('user.id').'/amcs/'
                    .$amcSession->get('user.id').'/orders',
                'includes' => ['property'],
                'auth' => 'customer',
                'body' => $data
            ]
        ];
    },
    'acceptOrderByAmc:init' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/accept',
                'auth' => 'amc'
            ]
        ];
    },
    'workflowCompleted' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/completed',
                'auth' => 'amc'
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                            && $data['extra']['newProcessStatus'] == ProcessStatus::COMPLETED;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::ACCEPTED,
                            'newProcessStatus' => ProcessStatus::COMPLETED
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::ACCEPTED,
                        'newProcessStatus' => ProcessStatus::COMPLETED
                    ]
                ]
            ]
        ];
    },
    'getCompletedLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Completed".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::ACCEPTED,
                        'newProcessStatus' => ProcessStatus::COMPLETED
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] === ProcessStatus::COMPLETED;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },
    'getCompleted' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'amc',
                'includes' => ['processStatus', 'completedAt']
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'processStatus' => 'completed',
                    'completedAt' => new Dynamic(Dynamic::DATETIME)
                ]
            ]
        ];
    },

    'workflowReviewed' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/reviewed',
                'auth' => 'amc'
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS;
                        }),
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::COMPLETED,
                            'newProcessStatus' => ProcessStatus::REVIEWED
                        ]
                    ]
                ],
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::COMPLETED,
                        'newProcessStatus' => ProcessStatus::REVIEWED
                    ]
                ]
            ]
        ];
    },

    'getReviewedLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Reviewed".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::COMPLETED,
                        'newProcessStatus' => ProcessStatus::REVIEWED
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::REVIEWED;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },

    'getReviewed' => function(Runtime $runtime){
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
                    'processStatus' => 'reviewed'
                ]
            ]
        ];
    },

    'workflowRevisionInReview' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/revision-in-review',
                'auth' => 'amc'
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS;
                        }),
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::REVIEWED,
                            'newProcessStatus' => ProcessStatus::REVISION_IN_REVIEW
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::REVIEWED,
                        'newProcessStatus' => ProcessStatus::REVISION_IN_REVIEW
                    ]
                ]
            ]
        ];
    },

    'getRevisionInReviewLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Revision In Review".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::REVIEWED,
                        'newProcessStatus' => ProcessStatus::REVISION_IN_REVIEW
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::REVISION_IN_REVIEW;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },


    'getRevisionInReview' => function(Runtime $runtime){
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
                    'processStatus' => 'revision-in-review'
                ]
            ]
        ];
    },

    'workflowRevisionPending' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/revision-pending',
                'auth' => 'amc'
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::REVISION_IN_REVIEW,
                            'newProcessStatus' => ProcessStatus::REVISION_PENDING
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::REVISION_IN_REVIEW,
                        'newProcessStatus' => ProcessStatus::REVISION_PENDING
                    ]
                ]
            ]
        ];
    },

    'getRevisionPendingLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Revision Pending".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::REVISION_IN_REVIEW,
                        'newProcessStatus' => ProcessStatus::REVISION_PENDING
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::REVISION_PENDING;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },


    'getRevisionPending' => function(Runtime $runtime){
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
                    'processStatus' => 'revision-pending'
                ]
            ]
        ];
    },

    'workflowOnHold' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/on-hold',
                'auth' => 'amc',
                'body' => [
                    'explanation' => 'I need to think.'
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                            && $data['extra']['newProcessStatus'] === ProcessStatus::ON_HOLD;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
                            'newProcessStatus' => ProcessStatus::ON_HOLD,
                            'explanation' => 'I need to think.'
                        ]
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:send-message',
                        'data' => new Dynamic(function($data){
                            return is_array($data);
                        })
                    ],
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
                        'newProcessStatus' => ProcessStatus::ON_HOLD
                    ],
                    [
                        'type' => 'order',
                        'event' => 'send-message',
                        'order' => $capture->get('createOrder.id'),
                        'message' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
        ];
    },

    'getPutOnHoldLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "On Hold".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
                        'newProcessStatus' => ProcessStatus::ON_HOLD,
                        'explanation' => 'I need to think.'
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::ON_HOLD;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },

    'getOnHold' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'amc',
                'includes' => ['processStatus', 'comment', 'putOnHoldAt']
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'processStatus' => 'on-hold',
                    'comment' => 'I need to think.',
                    'putOnHoldAt' => new Dynamic(Dynamic::DATETIME)
                ]
            ]
        ];
    },

    'workflowOnHoldWithoutExplanation' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/on-hold',
                'auth' => 'amc'
            ],
        ];
    },

    'getOnHoldWithoutExplanation' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'amc',
                'includes' => ['processStatus', 'comment']
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'processStatus' => ProcessStatus::ON_HOLD,
                    'comment' =>  null
                ]
            ]
        ];
    },

    'workflowResume' => function (Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/workflow/resume',
                'auth' => 'amc',
                'includes' => ['processStatus']
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                            && $data['extra']['newProcessStatus'] === ProcessStatus::REVISION_PENDING;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::ON_HOLD,
                            'newProcessStatus' => ProcessStatus::REVISION_PENDING,
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::ON_HOLD,
                        'newProcessStatus' => ProcessStatus::REVISION_PENDING
                    ]
                ]
            ]
        ];
    },

    'getResumeLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Revision Pending".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::ON_HOLD,
                        'newProcessStatus' => ProcessStatus::REVISION_PENDING
                    ]
                ],
                'filter' => new CompositeFilter([
                    new LastFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::REVISION_PENDING;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },


    'getResume' => function(Runtime $runtime){
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
                    'processStatus' => ProcessStatus::REVISION_PENDING
                ]
            ]
        ];
    },

    'workflowLate' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/late',
                'auth' => 'amc'
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
                            'newProcessStatus' => ProcessStatus::LATE
                        ]
                    ]
                ]
            ],

            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
                        'newProcessStatus' => ProcessStatus::LATE
                    ]
                ]
            ]
        ];
    },

    'getLateLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Late".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
                        'newProcessStatus' => ProcessStatus::LATE
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::LATE;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },

    'getLate' => function(Runtime $runtime){
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
                    'processStatus' => 'late'
                ]
            ]
        ];
    },

    'workflowReadyForReview' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/ready-for-review',
                'auth' => 'amc'
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS;
                        }),
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::LATE,
                            'newProcessStatus' => ProcessStatus::READY_FOR_REVIEW
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::LATE,
                        'newProcessStatus' => ProcessStatus::READY_FOR_REVIEW
                    ]
                ]
            ]
        ];
    },

    'getReadyForReviewLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Ready For Review".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::LATE,
                        'newProcessStatus' => ProcessStatus::READY_FOR_REVIEW
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::READY_FOR_REVIEW;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },

    'getReadyForReview' => function(Runtime $runtime){
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

    'workflowInspectionCompleted' => function(Runtime $runtime) use ($completedAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/inspection-completed',
                'auth' => 'amc',
                'body' => [
                    'completedAt' => $completedAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::READY_FOR_REVIEW,
                        'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                        'estimatedCompletionDate' => $estimatedCompletionDate,
                        'completedAt' => $completedAt
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                            && $data['extra']['newProcessStatus'] === ProcessStatus::INSPECTION_COMPLETED;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::READY_FOR_REVIEW,
                            'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                            'completedAt' => $completedAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ],
        ];
    },

    'getInspectionCompletedLogs' => function(Runtime $runtime) use ($completedAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Inspection Completed".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::READY_FOR_REVIEW,
                        'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                        'completedAt' => $completedAt,
                        'estimatedCompletionDate' => $estimatedCompletionDate
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::INSPECTION_COMPLETED;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },


    'getInspectionCompleted' => function(Runtime $runtime) use ($completedAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'amc',
                'includes' => [
                    'inspectionCompletedAt',
                    'estimatedCompletionDate',
                    'processStatus'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'inspectionCompletedAt' => $completedAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'processStatus' => 'inspection-completed'
                ]
            ]
        ];
    },

    'onHoldAfterInspectionCompleted:init' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/on-hold',
                'auth' => 'amc',
            ]
        ];
    },

    'workflowResumeInspectionCompleted' => function (Runtime $runtime) use ($completedAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/workflow/resume',
                'auth' => 'amc',
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => Action::UPDATE_PROCESS_STATUS,
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::ON_HOLD,
                        'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                        'estimatedCompletionDate' => $estimatedCompletionDate,
                        'completedAt' => $completedAt
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                            && $data['extra']['newProcessStatus'] === ProcessStatus::INSPECTION_COMPLETED;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::ON_HOLD,
                            'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                            'completedAt' => $completedAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ],
        ];
    },

    'tryWorkflowInspectionScheduled1' => function(Runtime $runtime) use ($dueDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        $scheduledAt = (new DateTime('-1 day'))->format(DateTime::ATOM);
        $estimatedCompletionDate = (new DateTime($dueDate))->format(DateTime::ATOM);

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/inspection-scheduled',
                'auth' => 'amc',
                'body' => [
                    'scheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ],
            'response' => [
                'errors' => [
                    'estimatedCompletionDate' => [
                        'identifier' => 'limit'
                    ]
                ],
                'filter' => new MessageAndExtraFilter()
            ]
        ];
    },

    'tryWorkflowInspectionScheduled3' => function(Runtime $runtime) use ($estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        $scheduledAt = new DateTime($estimatedCompletionDate);
        $scheduledAt->modify('+1 day');
        $scheduledAt = $scheduledAt->format(DateTime::ATOM);

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/inspection-scheduled',
                'auth' => 'amc',
                'body' => [
                    'scheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ],
            'response' => [
                'errors' => [
                    'scheduledAt' => [
                        'identifier' => 'limit'
                    ]
                ],
                'filter' => new MessageAndExtraFilter()
            ]
        ];
    },
    'workflowInspectionScheduled' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/inspection-scheduled',
                'auth' => 'amc',
                'body' => [
                    'scheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                        'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                        'estimatedCompletionDate' => $estimatedCompletionDate,
                        'scheduledAt' => $scheduledAt
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                                && $data['extra']['newProcessStatus'] === ProcessStatus::INSPECTION_SCHEDULED;
                        })
                    ],

                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                            'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                            'scheduledAt' => $scheduledAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ],
        ];
    },

    'getInspectionScheduledLogs' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Inspection Scheduled".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
                        'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                        'scheduledAt' => $scheduledAt,
                        'estimatedCompletionDate' => $estimatedCompletionDate
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::INSPECTION_SCHEDULED;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },

    'getInspectionScheduled' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'amc',
                'includes' => [
                    'inspectionScheduledAt',
                    'estimatedCompletionDate',
                    'processStatus'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'inspectionScheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'processStatus' => 'inspection-scheduled'
                ]
            ]
        ];
    },

    'onHoldAfterInspectionScheduled:init' => function (Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/on-hold',
                'auth' => 'amc'
            ]
        ];
    },

    'workflowResumeInspectionScheduled' => function (Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/resume',
                'auth' => 'amc'
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => Action::UPDATE_PROCESS_STATUS,
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::ON_HOLD,
                        'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                        'estimatedCompletionDate' => $estimatedCompletionDate,
                        'scheduledAt' => $scheduledAt
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                                && $data['extra']['newProcessStatus'] === ProcessStatus::INSPECTION_SCHEDULED;
                        })
                    ],

                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::ON_HOLD,
                            'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                            'scheduledAt' => $scheduledAt,
                            'estimatedCompletionDate' => $estimatedCompletionDate
                        ]
                    ]
                ]
            ]
        ];
    },

    'workflowAccepted' => function(Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/accepted',
                'auth' => 'amc',
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                        'newProcessStatus' => ProcessStatus::ACCEPTED
                    ]
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS
                            && $data['extra']['newProcessStatus'] === ProcessStatus::ACCEPTED;
                        }),
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                            'newProcessStatus' => ProcessStatus::ACCEPTED,
                        ]
                    ]
                ]
            ],
        ];
    },

    'getAccepted' => function(Runtime $runtime){
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
                    'processStatus' => 'accepted'
                ]
            ]
        ];
    },

    'getAcceptedLogs' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Accepted".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
                        'newProcessStatus' => ProcessStatus::ACCEPTED
                    ]
                ],
                'filter' => new CompositeFilter([
                    new LastFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::ACCEPTED;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },

    'workflowRequestForBid' => function(Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/request-for-bid',
                'auth' => 'amc',
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS;
                        }),
                    ],

                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::ACCEPTED,
                            'newProcessStatus' => ProcessStatus::REQUEST_FOR_BID,
                        ]
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::ACCEPTED,
                        'newProcessStatus' => ProcessStatus::REQUEST_FOR_BID
                    ]
                ]
            ]
        ];
    },

    'getRequestForBidLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "Request For Bid".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::ACCEPTED,
                        'newProcessStatus' => ProcessStatus::REQUEST_FOR_BID
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::REQUEST_FOR_BID;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },

    'getRequestForBid' => function(Runtime $runtime){
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
                    'processStatus' => 'request-for-bid'
                ]
            ]
        ];
    },

    'workflowNew' => function(Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/new',
                'auth' => 'amc',
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($data){
                            return $data['action'] === Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$session->get('user.id')
                        ],
                        'event' => 'order:update-process-status',
                        'data' => [
                            'order' => new Dynamic(function($data) use ($capture){
                                return $data['id'] == $capture->get('createOrder.id');
                            }),
                            'oldProcessStatus' => ProcessStatus::REQUEST_FOR_BID,
                            'newProcessStatus' => ProcessStatus::FRESH,
                        ]
                    ]
                ],
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'order',
                        'event' => 'update-process-status',
                        'order' => $capture->get('createOrder.id'),
                        'oldProcessStatus' => ProcessStatus::REQUEST_FOR_BID,
                        'newProcessStatus' => ProcessStatus::FRESH
                    ]
                ]
            ]
        ];
    },

    'getNew' => function(Runtime $runtime){
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
                    'processStatus' => ProcessStatus::FRESH
                ]
            ]
        ];
    },

    'getNewLogs' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
                'auth' => 'amc',
                'parameters' => [
                    'perPage' => 1000,
                ]
            ],
            'response' => [
                'body' => [
                    'action' => Action::UPDATE_PROCESS_STATUS,
                    'message' => sprintf(
                        '%s has changed the process status to "New".',
                        $session->get('user.companyName')
                    ),
                    'extra' => [
                        'user' => $session->get('user.companyName'),
                        'oldProcessStatus' => ProcessStatus::REQUEST_FOR_BID,
                        'newProcessStatus' => ProcessStatus::FRESH
                    ]
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v){
                        return $v['action'] === Action::UPDATE_PROCESS_STATUS
                            && $v['extra']['newProcessStatus'] == ProcessStatus::FRESH;
                    }),
                    new ItemFieldsFilter(['action', 'extra', 'message'], true)
                ])
            ]
        ];
    },
];
