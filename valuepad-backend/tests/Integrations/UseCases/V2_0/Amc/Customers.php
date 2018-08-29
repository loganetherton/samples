<?php
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$customer1 = [
    'username' => 'amccustomertest1',
    'password' => 'password',
    'name' => 'a0order'
];

$customer2 = [
    'username' => 'amccustomertest2',
    'password' => 'password',
    'name' => 'z9order'
];

return [
    'createCustomer1:init' => [
        'request' => [
            'url' => 'POST /customers',
            'body' => $customer1
        ]
    ],

    'createCustomer2:init' => [
        'request' => [
            'url' => 'POST /customers',
            'body' => $customer2
        ]
    ],

    'connect:init' => function(Runtime $runtime){
        return [
            'raw' => function(CustomerService $customerService) use ($runtime){
                $customerService->relateWithAmc(
                    $runtime->getCapture()->get('createCustomer1.id'),
                    $runtime->getSession('amc')->get('user.id')
                );

                $customerService->relateWithAmc(
                    $runtime->getCapture()->get('createCustomer2.id'),
                    $runtime->getSession('amc')->get('user.id')
                );
            }
        ];
    },

    'signinCustomer1:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $customer1
        ]
    ],

    'signinCustomer2:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $customer2
        ]
    ],

    'addAdditionalStatuses1:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/settings/additional-statuses',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer1.token')
                ],
                'body' => [
                    'title' => 'New Additional Status',
                    'comment' => 'New additional status for testing'
                ]
            ]
        ];
    },

    'addAdditionalStatuses2:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/settings/additional-statuses',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer1.token')
                ],
                'body' => [
                    'title' => 'Another Additional Status',
                    'comment' => 'Another additional status for testing'
                ]
            ]
        ];
    },

    'addAdditionalStatuses3:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/settings/additional-statuses',
                'auth' => 'guest',
                'headers' => [
                    'token' => $capture->get('signinCustomer2.token')
                ],
                'body' => [
                    'title' => 'YADS',
                    'comment' => 'Yet another additional status'
                ]
            ]
        ];
    },

    'addAdditionalDocumentsType1:init' => function(Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer1.id')
                    .'/settings/additional-documents/types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer1.token')
                ],
                'body' => [
                    'title' => 'Test type'
                ]
            ]
        ];
    },

    'addAdditionalDocumentsType2:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer2.id')
                    .'/settings/additional-documents/types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer2.token')
                ],
                'body' => [
                    'title' => 'New document type'
                ]
            ]
        ];
    },

    'addAdditionalDocumentsType3:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$capture->get('createCustomer2.id')
                    .'/settings/additional-documents/types',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinCustomer2.token')
                ],
                'body' => [
                    'title' => 'More document type'
                ]
            ]
        ];
    },

    'getCustomersDesc' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/customers',
                'parameters' => [
                    'orderBy' => 'name:desc'
                ],
                'auth' => 'amc'
            ],
            'response' => [
                'total' => ['>=', 2],
                'body' =>  [
                    'name' => 'z9order'
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) {
                        return true;
                    }),
                    new ItemFieldsFilter(['name'], true)
                ])
            ]
        ];
    },

    'getCustomersAsc' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/customers',
                'parameters' => [
                    'orderBy' => 'name:asc'
                ],
                'auth' => 'amc'
            ],
            'response' => [
                'total' => ['>=', 2],
                'body' =>  [
                    'name' => 'a0order'
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) {
                        return true;
                    }),
                    new ItemFieldsFilter(['name'], true)
                ])
            ]
        ];
    },

    'getAdditionalStatuses' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/customers/'
                    .$capture->get('createCustomer1.id').'/additional-statuses',
                'auth' => 'amc'
            ],
            'response' => [
                'body' => [
                    $capture->get('addAdditionalStatuses1'),
                    $capture->get('addAdditionalStatuses2')
                ]
            ]
        ];
    },

    'getAdditionalDocumentsTypes' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/customers/'
                    .$capture->get('createCustomer2.id').'/additional-documents/types',
                'auth' => 'amc'
            ],
            'response' => [
                'body' => [
                    $capture->get('addAdditionalDocumentsType2'),
                    $capture->get('addAdditionalDocumentsType3')
                ]
            ]
        ];
    },
];
