<?php
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Permission;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$appraiser = uniqid('appraiser');
$manager = uniqid('manager');

return [
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

    'createAppraiser:init' => function(Runtime $runtime) use ($appraiser) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser,
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

    'signinAppraiser:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => ['username' => $appraiser, 'password' => 'password']
        ]
    ],

    'createCompany:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'Accounting Summary for Managers',
                    'firstName' => 'aoi',
                    'lastName' => 'kaze',
                    'email' => 'i@pid.com',
                    'phone' => '(111) 123-2897',
                    'fax' => '(112) 123-8237',
                    'address1' => 'thesis',
                    'city' => 'budget',
                    'zip' => '99987',
                    'assignmentZip' => '11123',
                    'state' => 'TX',
                    'taxId' => '99-3322115',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'ach' => [
                        'bankName' => 'sadfasdfwe',
                        'accountNumber' => '11122221122',
                        'accountType' => AchAccountType::CHECKING,
                        'routing' => '123221232'
                    ],
                    'w9' => $capture->get('createW9')
                ]
            ]
        ];
    },

    'createBranch:init' => function (Runtime $runtime)  {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'Branching Branch',
                    'taxId' => '11-6457815',
                    'address1' => 'Light',
                    'city' => 'Abilene',
                    'state' => 'TX',
                    'zip' => '87545',
                    'assignmentZip' => '15648'
                ]
            ]
        ];
    },

    'getStaff:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/staff',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ]
        ];
    },

    'createManager:init' => function (Runtime $runtime) use ($manager) {
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'user' => [
                        'username' => $manager,
                        'password' => 'secret',
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'email' => 'xxxxx@yyyyyyy.com',
                        'phone' => '(999) 242-2211',
                    ],
                    'branch' => $runtime->getCapture()->get('createBranch.id'),
                    'notifyUser' => false,
                    'isManager' => true,
                    'isRfpManager' => true,
                    'isAdmin' => false
                ]
            ],
        ];
    },

    'signinManager:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => ['username' => $manager, 'password' => 'secret']
        ]
    ],

    'addPermissions:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'raw' => function (EntityManagerInterface $em) use ($capture) {
                $manager = $em->find(Staff::class, $capture->get('createManager.id'));
                $appraiser = $em->find(Staff::class, $capture->get('getStaff.0.id'));

                $permission = new Permission();
                $permission->setManager($manager);
                $permission->setAppraiser($appraiser);

                $em->persist($permission);
                $em->flush();
            }
        ];
    },

    'createOrderCompany:init' => function (Runtime $runtime) {
        $order = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        $customer = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$customer->get('user.id').'/companies/'
                    .$capture->get('createCompany.id').'/staff/'.$capture->get('getStaff.0.id').'/orders',
                'auth' => 'customer',
                'body' => $order
            ]
        ];
    },

    'createOrderInvididual:init' => function (Runtime $runtime) {
        $order = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        $customer = $runtime->getSession('customer');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$customer->get('user.id').'/appraisers/'
                    .$capture->get('createAppraiser.id').'/orders',
                'auth' => 'customer',
                'body' => $order
            ]
        ];
    },

    'getSummary' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/orders/totals',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    'paid' => [
                        'total' => 0,
                        'fee' => 0,
                        'techFee' => 0,
                    ],
                    'unpaid' => [
                        'total' => 1,
                        'fee' => 1000,
                        'techFee' => 0,
                    ],
                ],
            ],
        ];
    },

    'markOrderAsPaid:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $customer = $runtime->getSession('customer');

        return [
            'request' => [
                'url' => 'PATCH /customers/'.$customer->get('user.id').'/orders/'
                    .$capture->get('createOrderCompany.id'),
                'auth' => 'customer',
                'body' => [
                    'isPaid' => true,
                    'paidAt' => (new DateTime())->format(DateTime::ATOM)
                ]
            ]
        ];
    },

    'getSummaryAfterPaid' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/orders/totals',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    'paid' => [
                        'total' => 1,
                        'fee' => 1000,
                        'techFee' => 0
                    ],
                    'unpaid' => [
                        'total' => 0,
                        'fee' => 0,
                        'techFee' => 0,
                    ]
                ]
            ]
        ];
    },
];
