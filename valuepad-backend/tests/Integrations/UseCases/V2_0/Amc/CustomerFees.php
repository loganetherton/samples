<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ArrayFieldsFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\Fee;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\JobType\Entities\JobType;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$customer = uniqid('customer');

return [
    'validate' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'POST /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees',
                'body' => [
                    'jobType' => 10000,
                    'amount' => -10.99
                ]
            ],
            'response' => [
                'errors' => [
                    'jobType' => [
                        'identifier' => 'access',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'amount' => [
                        'identifier' => 'greater',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },
    'create1' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'POST /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees',
                'body' => [
                    'jobType' => 10,
                    'amount' => 10.99
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'jobType' => [
                        'id' => 10,
                        'isCommercial' => false,
                        'isPayable' => true,
                        'title' => new Dynamic(Dynamic::STRING),
                        'local' => new Dynamic(function($v){
                            return is_array($v);
                        })
                    ],
                    'amount' => 10.99
                ]
            ]
        ];
    },
    'tryCreate2' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'POST /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees',
                'body' => [
                    'jobType' => 10,
                    'amount' => 10.99
                ]
            ],
            'response' => [
                'errors' => [
                    'jobType' => [
                        'identifier' => 'already-taken',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },
    'create2' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'POST /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees',
                'body' => [
                    'jobType' => 13,
                    'amount' => 0.99
                ]
            ]
        ];
    },

    'getAll' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'GET /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees',
            ],
            'response' => [
                'total' => 2
            ]
        ];
    },
    'tryUpdate' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PATCH /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/'
                    .$capture->get('create2.id'),
                'body' => [
                    'jobType' => 14,
                    'amount' => 0.99
                ]
            ],
            'response' => [
                'errors' => [
                    'jobType' => [
                        'identifier' => 'read-only',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },
    'update' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PATCH /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/'
                    .$capture->get('create2.id'),
                'body' => [
                    'amount' => 40.45
                ]
            ]
        ];
    },
    'getAllAfterUpdating' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'GET /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees',
            ],
            'response' => [
                'body' => [
                    'amount' => 40.45
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function($k, $v) use ($capture){
                        return $v['id'] == $capture->get('create2.id');
                    }),
                    new ItemFieldsFilter(['amount'], true)
                ])
            ]
        ];
    },

    'delete' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'DELETE /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/'
                    .$capture->get('create1.id'),
            ]
        ];
    },
    'getAllAfterDeleting' => function(Runtime $runtime){
        $capture = $runtime->getCapture();
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'GET /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees',
            ],
            'response' => [
                'total' => 1,
                'assert' => function(Response $response) use ($capture){
                    return $response->getBody()[0]['id'] == $capture->get('create2.id');
                }
            ]
        ];
    },

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

    'addJobTypeFromCustomer:init' => function(Runtime $runtime){
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

    'connect:init' => function(Runtime $runtime){
        return [
            'raw' => function(CustomerService $customerService) use ($runtime){
                $customerService->relateWithAmc(
                    $runtime->getCapture()->get('createCustomer.id'),
                    $runtime->getSession('amc')->get('user.id')
                );
            }
        ];
    },

    'create3' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'POST /amcs/'
                    .$session->get('user.id').'/customers/'
                    .$capture->get('createCustomer.id').'/fees',
                'body' => [
                    'jobType' => $capture->get('addJobTypeFromCustomer.id'),
                    'amount' => 0.99
                ]
            ]
        ];
    },

    'enableJobType:init' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');

        return [
            'raw' => function (EntityManagerInterface $em) use ($session) {
                $amc = $em->find(Amc::class, $session->get('user.id'));
                // Customer job type #13 maps to local job type #14
                $jobType = $em->find(JobType::class, 14);

                $fee = new Fee();
                $fee->setAmc($amc);
                $fee->setJobType($jobType);
                $fee->setAmount(1.00);

                $em->persist($fee);
                $em->flush();
            }
        ];
    },

    'createStateFees:init' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PUT /amcs/'.$session->get('user.id').'/fees/14/states',
                'body' => [
                    'data' => [
                        ['state' => 'CA', 'amount' => 2]
                    ]
                ]
            ]
        ];
    },

    'createCountyFees:init' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PUT /amcs/'.$session->get('user.id').'/fees/14/states/CA/counties',
                'body' => [
                    'data' => [
                        ['county' => 194, 'amount' => 33.33],
                    ]
                ]
            ]
        ];
    },

    'createZipFees:init' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PUT /amcs/'.$session->get('user.id').'/fees/14/states/CA/zips',
                'body' => [
                    'data' => [
                        ['zip' => '90009', 'amount' => 12.12],
                    ]
                ]
            ]
        ];
    },

    'createCustomerCountyFee:init' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PUT /amcs/'.$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/13/states/CA/counties',
                'body' => [
                    'data' => [
                        ['county' => 194, 'amount' => 200]
                    ]
                ]
            ]
        ];
    },

    'createCustomerZipFee:init' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PUT /amcs/'.$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/13/states/CA/zips',
                'body' => [
                    'data' => [
                        ['zip' => '90210', 'amount' => 11]
                    ]
                ]
            ]
        ];
    },

    'applyDefaultLocationFees' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'PUT /amcs/'.$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/apply-default-location-fees'
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ]
        ];
    },

    'getStateFees' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'GET /amcs/'.$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/13/states'
            ],
            'response' => [
                'body' => [
                    ['state' => ['code' => 'CA'], 'amount' => 2]
                ],
                'filter' => new ArrayFieldsFilter(['state.code', 'amount'], true)
            ]
        ];
    },

    'getCountyFees' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'GET /amcs/'.$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/13/states/CA/counties'
            ],
            'response' => [
                'body' => [
                    ['county' => ['id' => 194], 'amount' => 33.33]
                ],
                'filter' => new ArrayFieldsFilter(['county.id', 'amount'], true)
            ]
        ];
    },

    'getZipFees' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'GET /amcs/'.$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/13/states/CA/zips'
            ],
            'response' => [
                'body' => [
                    ['zip' => '90210', 'amount' => 11],
                    ['zip' => '90009', 'amount' => 12.12],
                ]
            ]
        ];
    },

    'deleteWithLocationFees' => function (Runtime $runtime) {
        $session = $runtime->getSession('amc');
        $customerSession = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'auth' => 'amc',
                'url' => 'DELETE /amcs/'.$session->get('user.id').'/customers/'
                    .$customerSession->get('user.id').'/fees/'.$capture->get('create2.id')
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ]
        ];
    }
];