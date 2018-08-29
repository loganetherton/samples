<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

$customer = uniqid('customer');

return [
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
                    'name' => 'Wonderful World',
                    'address1' => 'XXX Street Ave.',
                    'address2' => 'Well Street Ave',
                    'city' => 'Cool City',
                    'state' => 'NV',
                    'zip' => '94222'
                ]
            ]
        ];
    },

    'addRuleset:init' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/rulesets',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'level' => 10,
                    'label' => 'Company XXX',
                    'rules' => [
                        'clientAddress1' => '422 Client Str',
                        'clientAddress2' => null,
                        'clientCity' => 'Client City',
                        'clientState' => 'TX',
                        'clientZip' => '44222',

                        'clientDisplayedOnReportAddress1' => '422 Client Displayed On Report Str',
                        'clientDisplayedOnReportAddress2' => null,
                        'clientDisplayedOnReportCity' => 'Client Displayed On Report City',
                        'clientDisplayedOnReportState' => 'OR',
                        'clientDisplayedOnReportZip' => '44422'
                    ]
                ]
            ]
        ];
    },

    'createOrder:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $appraiserSession = $runtime->getSession('appraiser');

        $data =  OrdersFixture::get($runtime->getHelper(), [
            'client' => $capture->get('addClient.id'),
            'clientDisplayedOnReport' => $capture->get('addClient.id')
        ]);

        $data['jobType'] = $capture->get('addJobType.id');

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$capture->get('createCustomer.id').'/appraisers/'
                    .$appraiserSession->get('user.id').'/orders',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'body' => $data
            ]
        ];
    },

    'getOrderWithoutRuleset' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'includes' => [
                    'clientAddress1', 'clientAddress2', 'clientCity', 'clientState', 'clientZip',
                    'clientDisplayedOnReportAddress1', 'clientDisplayedOnReportAddress2',
                    'clientDisplayedOnReportCity', 'clientDisplayedOnReportState', 'clientDisplayedOnReportZip'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),

                    'clientAddress1' => 'XXX Street Ave.',
                    'clientAddress2' => 'Well Street Ave',
                    'clientCity' => 'Cool City',
                    'clientState' => [
                        'code' => 'NV',
                        'name' => 'Nevada'
                    ],
                    'clientZip' => '94222',

                    'clientDisplayedOnReportAddress1' => 'XXX Street Ave.',
                    'clientDisplayedOnReportAddress2' => 'Well Street Ave',
                    'clientDisplayedOnReportCity' => 'Cool City',
                    'clientDisplayedOnReportState' => [
                        'code' => 'NV',
                        'name' => 'Nevada'
                    ],
                    'clientDisplayedOnReportZip' => '94222',
                ]
            ]
        ];
    },

    'orderPatch:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$capture->get('createCustomer.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'body' => [
                    'rulesets' => [$capture->get('addRuleset.id')]
                ]
            ]
        ];
    },

    'getOrderWithRuleset' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/orders/'.$capture->get('createOrder.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer.token')
                ],
                'includes' => [
                    'clientAddress1', 'clientAddress2', 'clientCity', 'clientState', 'clientZip',
                    'clientDisplayedOnReportAddress1', 'clientDisplayedOnReportAddress2',
                    'clientDisplayedOnReportCity', 'clientDisplayedOnReportState', 'clientDisplayedOnReportZip'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder.id'),
                    'fileNumber' => $capture->get('createOrder.fileNumber'),

                    'clientAddress1' => '422 Client Str',
                    'clientAddress2' => null,
                    'clientCity' => 'Client City',
                    'clientState' => [
                        'code' => 'TX',
                        'name' => 'Texas'
                    ],
                    'clientZip' => '44222',

                    'clientDisplayedOnReportAddress1' => '422 Client Displayed On Report Str',
                    'clientDisplayedOnReportAddress2' => null,
                    'clientDisplayedOnReportCity' => 'Client Displayed On Report City',
                    'clientDisplayedOnReportState' => [
                        'code' => 'OR',
                        'name' => 'Oregon'
                    ],
                    'clientDisplayedOnReportZip' => '44422',
                ]
            ]
        ];
    }
];