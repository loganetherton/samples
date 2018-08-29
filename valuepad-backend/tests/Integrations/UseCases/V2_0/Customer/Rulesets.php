<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

return [
    'validate' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/rulesets',
                'auth' => 'customer',
                'body' => [
                    'label' => ' ',
                    'rules' => [
                        'flagWrong' => 10
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'level' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'label' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'rules' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'create' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/rulesets',
                'auth' => 'customer',
                'body' => [
                    'level' => 20,
                    'label' => 'Company XXX',
                    'rules' => [
                        'requireEnv' => true
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'level' => 20,
                    'label' => 'Company XXX',
                    'rules' => [
                        'requireEnv' => true
                    ]
                ]
            ]
        ];
    },

    'get' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'level' => 20,
                    'label' => 'Company XXX',
                    'rules' => [
                        'requireEnv' => true
                    ]
                ]
            ]
        ];
    },


    'validateRulesWhenUpdate' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer',
                'body' => [
                    'rules' => [
                        'requireEnv' => 1312
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'rules' => [
                        'identifier' => 'dataset',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => [
                            'requireEnv' => [
                                'identifier' => 'cast',
                                'message' => new Dynamic(Dynamic::STRING),
                                'extra' => []
                            ]
                        ]
                    ]
                ]
            ]
        ];
    },

    'update' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer',
                'body' => [
                    'level' => 10,
                    'label' => 'Company ZZZ',
                    'rules' => [
                        'requireEnv' => false
                    ]
                ]
            ]
        ];
    },

    'getUpdated' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'level' => 10,
                    'label' => 'Company ZZZ',
                    'rules' => [
                        'requireEnv' => false
                    ]
                ]
            ]
        ];
    },

    'removeRules' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer',
                'body' => [
                    'rules' => []
                ]
            ]
        ];
    },

    'getWithRemovedRules' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer'
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'level' => 10,
                    'label' => 'Company ZZZ',
                    'rules' => []
                ]
            ]
        ];
    },

    'delete' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'DELETE /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer'
            ]
        ];
    },

    'getDeleted' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/rulesets/'.$capture->get('create.id'),
                'auth' => 'customer'
            ],
            'response' => [
                'status' => 404
            ]
        ];
    },

    'createLevel1:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/rulesets',
                'auth' => 'customer',
                'body' => [
                    'level' => 1,
                    'label' => 'Company XXX',
                    'rules' => [
                        'requireEnv' => true
                    ]
                ]
            ]
        ];
    },

    'createLevel2:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/rulesets',
                'auth' => 'customer',
                'body' => [
                    'level' => 20,
                    'label' => 'Branch of Company XXX',
                    'rules' => [
                        'requireEnv' => true
                    ]
                ]
            ]
        ];
    },

    'createLevel3:init' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'POST /customers/'.$session->get('user.id').'/rulesets',
                'auth' => 'customer',
                'body' => [
                    'level' => 40,
                    'label' => 'User of Branch of Company XXX',
                    'rules' => [
                        'requireEnv' => false
                    ]
                ]
            ]
        ];
    },

    'createOrder:init' => function(Runtime $runtime){
        $customerSession = $runtime->getSession('customer');
        $appraiserSession = $runtime->getSession('appraiser');
        $capture = $runtime->getCapture();

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        $data['rulesets'] = [
            $capture->get('createLevel3.id'),
            $capture->get('createLevel2.id'),
            $capture->get('createLevel1.id'),
        ];

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

    'getOrderWithRules' => function(Runtime $runtime){
        $session = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'customer',
                'includes' => ['rules']
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),
                    'rules' => [
                        'requireEnv' => false
                    ]
                ]
            ]
        ];
    }
];
