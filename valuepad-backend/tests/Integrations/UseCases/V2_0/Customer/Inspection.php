<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;


$inspectionScheduledAt = (new DateTime('-6 months'))->format(DateTime::ATOM);
$inspectionCompletedAt = (new DateTime('-5 months'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('-4 months'))->format(DateTime::ATOM);

return [
    'createOrder:init' => function(Runtime $runtime){
        $customerSession = $runtime->getSession('customer');
        $appraiserSession = $runtime->getSession('appraiser');

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customerSession->get('user.id').'/appraisers/'
                    .$appraiserSession->get('user.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::get($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    'specifyInspectionDates' => function(Runtime $runtime) use ($inspectionCompletedAt, $inspectionScheduledAt, $estimatedCompletionDate){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'body' => [
                    'inspectionCompletedAt' => $inspectionCompletedAt,
                    'inspectionScheduledAt' => $inspectionScheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ]
        ];
    },

    'verifyInspectionDates' => function(Runtime $runtime) use ($inspectionCompletedAt, $inspectionScheduledAt, $estimatedCompletionDate){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'includes' => ['inspectionScheduledAt', 'inspectionCompletedAt', 'estimatedCompletionDate']
            ],
            'response' => [
                'body' => [
                    'id' => $runtime->getCapture()->get('createOrder.id'),
                    'fileNumber' => $runtime->getCapture()->get('createOrder.fileNumber'),
                    'inspectionCompletedAt' => $inspectionCompletedAt,
                    'inspectionScheduledAt' => $inspectionScheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ]
        ];
    },
    'unsetInspectionDates' => function(Runtime $runtime) use ($inspectionCompletedAt, $inspectionScheduledAt){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'body' => [
                    'inspectionCompletedAt' => null,
                    'inspectionScheduledAt' => null,
                    'estimatedCompletionDate' => null
                ]
            ]
        ];
    },
    'verifyUnsetInspectionDates' => function(Runtime $runtime) {
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'includes' => ['inspectionScheduledAt', 'inspectionCompletedAt', 'estimatedCompletionDate']
            ],
            'response' => [
                'body' => [
                    'id' => $runtime->getCapture()->get('createOrder.id'),
                    'fileNumber' => $runtime->getCapture()->get('createOrder.fileNumber'),
                    'inspectionCompletedAt' => null,
                    'inspectionScheduledAt' => null,
                    'estimatedCompletionDate' => null
                ]
            ]
        ];
    },
    'wrongInspectionDates' => function(Runtime $runtime) use ($inspectionCompletedAt, $inspectionScheduledAt){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'body' => [
                    'inspectionCompletedAt' => (new DateTime('+1 year'))->format(DateTime::ATOM),
                    'inspectionScheduledAt' => (new DateTime('+1 year'))->format(DateTime::ATOM),
                    'estimatedCompletionDate' => (new DateTime('+4 year'))->format(DateTime::ATOM)
                ]
            ],
            'response' => [
                'errors' => [
                    'inspectionScheduledAt' => [
                        'identifier' => 'limit',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'inspectionCompletedAt' => [
                        'identifier' => 'limit',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'estimatedCompletionDate' => [
                        'identifier' => 'limit',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },
    'specifyInspectionDatesForDueDate:init' => function(Runtime $runtime) {
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'body' => [
                    'inspectionCompletedAt' => (new DateTime('-10 days'))->format(DateTime::ATOM),
                    'inspectionScheduledAt' => (new DateTime('-11 days'))->format(DateTime::ATOM),
                    'estimatedCompletionDate' => (new DateTime('-9 days'))->format(DateTime::ATOM)
                ]
            ]
        ];
    },

    'tryChangeWrongDueDate' => function(Runtime $runtime){

        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'body' => [
                    'dueDate' => (new DateTime('-12 days'))->format(DateTime::ATOM),
                ]
            ],
            'response' => [
                'errors' => [
                    'inspectionScheduledAt' => [
                        'identifier' => 'limit',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'inspectionCompletedAt' => [
                        'identifier' => 'limit',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'estimatedCompletionDate' => [
                        'identifier' => 'limit',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'workflowInspectionScheduled:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/inspection-scheduled',
                'auth' => 'customer',
                'body' => [
                    'scheduledAt' => (new DateTime('-10 days'))->format(DateTime::ATOM),
                    'estimatedCompletionDate' => (new DateTime('+10 days'))->format(DateTime::ATOM)
                ]
            ]
        ];
    },

    'tryUnsetInspectionScheduledAt' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'body' => [
                    'inspectionCompletedAt' => null,
                    'inspectionScheduledAt' => null,
                    'estimatedCompletionDate' => null
                ]
            ],
            'response' => [
                'errors' => [
                    'inspectionScheduledAt' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'estimatedCompletionDate' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'workflowInspectionCompletedAt:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/workflow/inspection-completed',
                'auth' => 'customer',
                'body' => [
                    'completedAt' => (new DateTime('-10 days'))->format(DateTime::ATOM),
                    'estimatedCompletionDate' => (new DateTime('+10 days'))->format(DateTime::ATOM)
                ]
            ]
        ];
    },

    'tryUnsetInspectionCompletedAt' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/orders/'.$runtime->getCapture()->get('createOrder.id'),
                'auth' => 'customer',
                'body' => [
                    'inspectionCompletedAt' => null,
                    'inspectionScheduledAt' => null,
                    'estimatedCompletionDate' => null
                ]
            ],
            'response' => [
                'errors' => [
                    'inspectionScheduledAt' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'inspectionCompletedAt' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'estimatedCompletionDate' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },
];
