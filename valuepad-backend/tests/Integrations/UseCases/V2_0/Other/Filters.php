<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Customer\Enums\ExtraFormat;
use ValuePad\Core\Customer\Enums\Format;
use Ascope\QA\Support\Response;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;

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
                    'jobType' => 11,
                    'primary' => [Format::PDF],
                    'extra' => [ExtraFormat::ZOO]
                ]
            ]
        ];
    },
    'addRuleset:init' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getSession('customer')->get('user.id').'/rulesets',
                'auth' => 'customer',
                'body' => [
                    'level' => 41,
                    'label' => 'Company XXX',
                    'rules' => [
                        'displayFdic' => false
                    ]
                ]
            ]
        ];
    },
    'createOrder:init' => function(Runtime $runtime) use ($dueDate){
        $customerSession = $runtime->getSession('customer');
        $appraiserSession = $runtime->getSession('appraiser');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);
        $data['jobType'] = 11;

        $data['rulesets'] = [(int) $runtime->getCapture()->get('addRuleset.id')];

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customerSession->get('user.id').'/appraisers/'
                    .$appraiserSession->get('user.id').'/orders',
                'auth' => 'customer',
                'body' => $data
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
    'createZoo:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.zoo'
            ]
        ]
    ],

    'accept:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('appraiser');

        return [
            'request' => [
                'url' => 'POST /appraisers/'
                    .$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/accept',
            ]
        ];
    },
    'scheduleInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
        $session = $runtime->getSession('appraiser');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/schedule-inspection',
                'body' => [
                    'scheduledAt' => $scheduledAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ]
        ];
    },
    'completeInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $completedAt){
        $session = $runtime->getSession('appraiser');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/complete-inspection',
                'body' => [
                    'completedAt' => $completedAt,
                    'estimatedCompletionDate' => $estimatedCompletionDate
                ]
            ]
        ];
    },

    'complete:init' => function(Runtime $runtime){
        $session = $runtime->getSession('appraiser');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/document',
                'body' => [
                    'primary' => [
                        'id' => $capture->get('createPdf.id'),
                        'token' => $capture->get('createPdf.token')
                    ],
                    'extra' => [
                        [
                            'id' => $capture->get('createZoo.id'),
                            'token' => $capture->get('createZoo.token')
                        ]
                    ]
                ]
            ]
        ];
    },

    'hideFromAppraiser:init' => function(Runtime $runtime){

        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/documents/'
                    .$capture->get('complete.id'),

                'auth' => 'customer',
                'body' => [
                    'showToAppraiser' => false
                ]
            ]
        ];
    },

    'getRestrictedDocument' => function(Runtime $runtime){
        $session = $runtime->getSession('appraiser');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/document'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'extra' => [
                        [
                            'id' => new Dynamic(Dynamic::INT),
                            'format' => ExtraFormat::ZOO
                        ]
                    ],
                    'showToAppraiser' => false
                ]
            ]
        ];
    },

    'create' => function(Runtime $runtime) {
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
                    .$capture->get('createOrder.id').'/documents',
                'auth' => 'customer',
                'body' => [
                    'primary' => [
                        'id' => $capture->get('createPdf.id'),
                        'token' => $capture->get('createPdf.token')
                    ],
                    'extra' => [
                        [
                            'id' => $capture->get('createZoo.id'),
                            'token' => $capture->get('createZoo.token')
                        ]
                    ],
                    'showToAppraiser' => false
                ]
            ],
            'live' => [
                'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function () { return true; })
                    ],
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:create-document',
                        'data' => [
                            'order' => [
                                'id' => new Dynamic(Dynamic::INT),
                                'fileNumber' => new Dynamic(Dynamic::STRING),
                            ],
                            'document' => [
                                'id' => new Dynamic(Dynamic::INT),
                                'extra' => [
                                    [
                                        'id' => new Dynamic(Dynamic::INT),
                                        'format' => ExtraFormat::ZOO
                                    ]
                                ],
                                'showToAppraiser' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
    },
    'getAppraiserFromCustomer' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getSession('appraiser')->get('user.id'),
                'includes' => ['taxIdentificationNumber', 'w9'],
                'auth' => 'customer'
            ],
            'response' => [
                'assert' => function(Response $response){
                    $body = $response->getBody();

                    if (!$body){
                        return false;
                    }

                    foreach ($body as $key => $value){
                        if (in_array($key, ['w9', 'taxIdentificationNumber'])){
                            return true;
                        }
                    }

                    return false;
                }
            ]
        ];
    },

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

    'createAnonymousAppraiser:init' => function(Runtime $runtime) {

        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => uniqid(),
            'password' => 'password',
            'w9' => [
                'id' => $capture->get('createW9.id'),
                'token' => $capture->get('createW9.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'TX'
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

    'getAnonymousAppraiserFromCustomer' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$runtime->getCapture()->get('createAnonymousAppraiser.id'),
                'includes' => ['taxIdentificationNumber', 'w9'],
                'auth' => 'customer'
            ],
            'response' => [
                'assert' => function(Response $response){
                    $body = $response->getBody();

                    if (!$body){
                        return false;
                    }

                    foreach ($body as $key => $value){
                        if (in_array($key, ['w9', 'taxIdentificationNumber'])){
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ];
    },

    'getCustomerFromAppraiser' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getSession('customer')->get('user.id'),
                'includes' => ['secret1', 'secret2'],
            ],
            'response' => [
                'assert' => function(Response $response){
                    $body = $response->getBody();

                    if (!$body){
                        return false;
                    }

                    foreach ($body as $key => $value){
                        if (in_array($key, ['secret1', 'secret2'])){
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ];
    },
    'getCustomerSettingsFromAppraiser' => function(Runtime $runtime){

        return [
            'request' => [
                'url' => 'GET /customers/'.$runtime->getSession('customer')->get('user.id').'/settings',
            ],
            'response' => [
                'assert' => function(Response $response){
                    $body = $response->getBody();

                    if (!$body){
                        return false;
                    }

                    foreach ($body as $key => $value){
                        if (in_array($key, ['pushUrl'])){
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ];
    },

    'hideClientFromAppraiser:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/settings',
                'body' => [
                    'showClientToAppraiser' => false
                ],
                'auth' => 'customer'
            ]
        ];
    },

    'getRestrictedOrder' => function(Runtime $runtime){

        $capture = $runtime->getCapture();
        $session = $runtime->getSession('appraiser');

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'includes' => [
                    'clientName', 'clientAddress1', 'clientAddress2',
                    'clientCity', 'clientState', 'clientZip', 'fdic'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber')
                ]
            ]
        ];
    },

    'showClientFromAppraiser:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/settings',
                'body' => [
                    'showClientToAppraiser' => true
                ],
                'auth' => 'customer'
            ]
        ];
    },

    'updateRuleset:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id')
                    .'/rulesets/'.$runtime->getCapture()->get('addRuleset.id'),
                'body' => [
                    'rules' => [
                        'displayFdic' => true
                    ]
                ],
                'auth' => 'customer'
            ]
        ];
    },

    'getNotRestrictedOrder' => function(Runtime $runtime){

        $capture = $runtime->getCapture();
        $session = $runtime->getSession('appraiser');

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'includes' => [
                    'clientName', 'clientAddress1', 'clientAddress2',
                    'clientCity', 'clientState', 'clientZip', 'fdic'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'clientName' => new Dynamic(Dynamic::STRING),
                    'clientAddress1' => new Dynamic(Dynamic::STRING),
                    'clientAddress2' => new Dynamic(Dynamic::STRING),
                    'clientCity' => new Dynamic(Dynamic::STRING),
                    'clientState' => [
                        'code' => new Dynamic(Dynamic::STRING),
                        'name' => new Dynamic(Dynamic::STRING)
                    ],
                    'clientZip' => new Dynamic(Dynamic::STRING),
                    'fdic' => null
                ]
            ]
        ];
    }
];