<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$customer = [
    'username' => 'Ayyyyyyyyy',
    'password' => 'LMAOOOO',
    'name' => 'meme.jpg',
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
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/NV/zips',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'zip' => '89019',
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
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/NV/zips',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                       [
                            'zip' => '89019',
                            'amount' => 55,
                       ],
                        [
                            'zip' => '22222',
                            'amount' => 100,
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
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/NV/zips',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'zip' => '89019',
                            'amount' => 55,
                        ],
                        [
                            'zip' => '89019',
                            'amount' => 5555,
                        ],
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
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/CA/zips',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'zip' => '94132',
                            'amount' => 100,
                        ],
                        [
                            'zip' => '94106',
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '94132',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '94106',
                        'amount' => 56,
                    ],
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
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/TX/zips',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'zip' => '75075',
                            'amount' => 100,
                        ],
                        [
                            'zip' => '75424',
                            'amount' => 56,
                        ],
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '75075',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '75424',
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'syncForeign' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType2.id').'/states/TX/zips',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'zip' => '75076',
                            'amount' => 100,
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
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/CA/zips',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '94132',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '94106',
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
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/TX/zips',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '75075',
                        'amount' => 100,
                    ],
                    [
                        'zip' => '75424',
                        'amount' => 56,
                    ],
                ]
            ]
        ];
    },

    'sync3' => function(Runtime $runtime) {
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/TX/zips',
                'auth' => 'amc',
                'body' => [
                    'data' => [
                        [
                            'zip' => '75075',
                            'amount' => 552,
                        ],
                    ]
                ]
            ]
        ];
    },
    'getAll3' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $amc = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees/'.$capture->get('addJobType1.id').'/states/TX/zips',
                'auth' => 'amc',
            ],
            'response' => [
                'body' => [
                    [
                        'zip' => '75075',
                        'amount' => 552,
                    ],
                ]
            ]
        ];
    },
];
