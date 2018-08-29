<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Response;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$customer = [
    'username' => 'mahdudeeeee',
    'password' => '56sdfdsf6+589',
    'name' => 'asdfewfwerew',
];

return [
    'createCustomer:init' => [
        'request' => [
            'url' => 'POST /customers',
            'body' => $customer
        ]
    ],

    'signinCustomer:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $customer
        ]
    ],

    'addJobType1:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'title' => 'PSDOIFJ',
                    'isCommercial' => false,
                    'local' => 8,
                    'isPayable' => true,
                ]
            ]
        ];
    },

    'addJobType2:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'title' => 'asdiofuhas',
                    'isCommercial' => false,
                    'local' => 9,
                    'isPayable' => true,
                ]
            ]
        ];
    },

    'connect:init' => function (Runtime $runtime) {
        $amc = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'raw' => function (CustomerService $customerService) use ($capture, $amc) {
                $customerService->relateWithAmc($capture->get('createCustomer.id'), $amc->get('user.id'));
            },
        ];
    },

    'createFees:init' => function(Runtime $runtime){
        $amc = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc->get('user.id').'/customers/'.$capture->get('createCustomer.id').'/fees',
                'auth' => 'amc',
                'body' => [
                    'bulk' => [
                        ['jobType' => $capture->get('addJobType1.id'), 'amount' => 2.01],
                        ['jobType' => $capture->get('addJobType2.id'), 'amount' => 3.02],
                    ]
                ]
            ]
        ];
    },

    'validate1' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => -12,
                        ]
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'data' => [
                        'identifier' => 'collection',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => [
                            [
                                'identifier' => 'dataset',
                                'message' => new Dynamic(Dynamic::STRING),
                                'extra' => [
                                    'amount' => [
                                        'identifier' => 'greater',
                                        'message' => new Dynamic(Dynamic::STRING),
                                        'extra' => []
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    },

    'validate2' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => 24,
                        ],
                        [
                            'state' => 'OO',
                            'amount' => 24,
                        ]
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'data' => [
                        'identifier' => 'exists',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'validate3' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => 24,
                        ],
                        [
                            'state' => 'CA',
                            'amount' => 24,
                        ]
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'data' => [
                        'identifier' => 'unique',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'sync1' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'state' => 'CA',
                            'amount' => 100,
                        ],
                        [
                            'state' => 'TX',
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'CA',
                            'name' => 'California'
                        ],
                        'amount' => 100,
                    ],
                    [
                        'state' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ],
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'tryUpdate' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');


        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/TX',
                'auth' => 'amc',
                'body' => [
                    'state' => 'CA'
                ]
            ],
            'response' => [
                'errors' => [
                    'state' => [
                        'identifier' => 'already-taken',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => [],
                    ]
                ]
            ]
        ];
    },

    'sync2' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType2.id').'/states',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'state' => 'TX',
                            'amount' => 98,
                        ],
                    ]
                ]
            ]
        ];
    },

    'getAll1' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'CA',
                            'name' => 'California'
                        ],
                        'amount' => 100,
                    ],
                    [
                        'state' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ],
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },
    'getAll2' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType2.id').'/states',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'TX',
                            'name' => 'Texas'
                        ],
                        'amount' => 98,
                    ],
                ]
            ]
        ];
    },

    'update2' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType2.id').'/states/TX',
                'auth' => 'amc',
                'body' => [
                    'state' => 'NV',
                    'amount' => 200,
                ]
            ]
        ];
    },

    'updateTheSameState' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType2.id').'/states/NV',
                'auth' => 'amc',
                'body' => [
                    'state' => 'NV',
                ]
            ]
        ];
    },

    'get2Updated' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType2.id').'/states',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => [
                    [
                        'state' => [
                            'code' => 'NV',
                            'name' => 'Nevada'
                        ],
                        'amount' => 200,
                    ],
                ]
            ]
        ];
    },

    'update2Wrong' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType2.id').'/states/TX',
                'auth' => 'amc',
                'body' => [
                    'amount' => 200,
                ]
            ],
            'response' => [
                'status' => Response::HTTP_NOT_FOUND
            ]
        ];
    },
];
