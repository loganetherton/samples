<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$appraiser = uniqid('appraiser');
$appraiser1 = uniqid('appraiser1');
$appraiser2 = uniqid('appraiser2');
$manager = uniqid('manager');
$estimatedCompletionDate = (new DateTime('+1 month'))->format('c');

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

    'createAppraiser:init' => function (Runtime $runtime) use ($appraiser) {
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
                    'state' => 'CA'
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

    'createAppraiser1:init' => function (Runtime $runtime) use ($appraiser1) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser1,
            'password' => 'password',
            'w9' => [
                'id' => $capture->get('createW9.id'),
                'token' => $capture->get('createW9.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'CA'
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

    'createAppraiser2:init' => function (Runtime $runtime) use ($appraiser2) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser2,
            'password' => 'password',
            'w9' => [
                'id' => $capture->get('createW9.id'),
                'token' => $capture->get('createW9.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'CA'
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
            'body' => [
                'username' => $appraiser,
                'password' => 'password'
            ]
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
                    'name' => 'The World Appraisal Company',
                    'firstName' => 'ayyy',
                    'lastName' => 'lmao',
                    'email' => 'hnnnng@yyyy.com',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'Ooooooo',
                    'city' => 'Uranus',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => '21-5467845',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'ach' => [
                        'bankName' => 'sadfasdfwe',
                        'accountNumber' => '11122221122',
                        'accountType' => AchAccountType::CHECKING,
                        'routing' => '123221232'
                    ],
                    'w9' => ['id' => $capture->get('createW9.id'), 'token' => $capture->get('createW9.token')],
                    'otherType' => 'Other company type',
                ]
            ],
        ];
    },

    'getBranches:init' => function (Runtime $runtime)  {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ]
        ];
    },

    'createManager:init' => function (Runtime $runtime) use ($manager) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'isRfpManager' => false,
                    'user' => [
                        'username' => $manager,
                        'password' => 'password',
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'email' => 'fdg4e@gmail.com',
                        'phone' => '(999) 242-2211',
                    ],
                    'branch' => $runtime->getCapture()->get('getBranches.0.id')
                ]
            ],
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

    'setAppraiserRfpManager:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany.id').'/staff/'.$capture->get('getStaff.0.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'isRfpManager' => true
                ]
            ],
        ];
    },

    'createOrder1:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$capture->get('createCompany.id').'/staff/'
                    .$capture->get('getStaff.0.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::getAsBidRequest($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    'trySubmitBidWithManagerAsAssignee' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
                    .$capture->get('createOrder1.id').'/bid',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'amount' => 999.99,
                    'estimatedCompletionDate' => (new DateTime('+1 month'))->format('c'),
                    'comments' => 'ezpz',
                    'appraisers' => [
                        $capture->get('createManager.user.id'),
                        $capture->get('createAppraiser2.id')
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'appraisers' => [
                        'identifier' => 'collection',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => [
                            [
                                'identifier' => 'not-appraiser',
                                'message' => new Dynamic(Dynamic::STRING),
                                'extra' => []
                            ],
                            [
                                'identifier' => 'not-belong',
                                'message' => new Dynamic(Dynamic::STRING),
                                'extra' => []
                            ]
                        ]
                    ]
                ]
            ]
        ];
    },

    'setManagerAsRfpManager:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany.id').'/staff/'.$capture->get('getStaff.1.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'isRfpManager' => true
                ]
            ],
        ];
    },

    'createOrder2:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$capture->get('createCompany.id').'/staff/'
                    .$capture->get('getStaff.1.id').'/orders',
                'auth' => 'customer',
                'body' => OrdersFixture::getAsBidRequest($runtime->getHelper(), [
                    'client' => 1,
                    'clientDisplayedOnReport' => 2
                ])
            ]
        ];
    },

    'signinManager:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $manager,
                'password' => 'password'
            ]
        ]
    ],

    'addSecondAppraiserToCompany:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'raw' => function (EntityManagerInterface $em) use ($capture) {
                $appraiser = $em->find(Appraiser::class, $capture->get('createAppraiser1.id'));
                $branch = $em->find(Branch::class, $capture->get('getBranches.0.id'));

                $staff = new Staff();
                $staff->setCompany($branch->getCompany());
                $staff->setBranch($branch);
                $staff->setUser($appraiser);

                $em->persist($staff);
                $em->flush();
            }
        ];
    },

    'trySubmitBidWithDuplicateAppraisers' => function (Runtime $runtime) use ($estimatedCompletionDate) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /managers/'.$capture->get('createManager.user.id').'/orders/'
                    .$capture->get('createOrder2.id').'/bid',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ],
                'body' => [
                    'amount' => 999.99,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'comments' => 'ezpz',
                    'appraisers' => [
                        $capture->get('createAppraiser.id'),
                        $capture->get('createAppraiser.id')
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'appraisers' => [
                        'identifier' => 'unique',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'submitBid' => function (Runtime $runtime) use ($estimatedCompletionDate) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /managers/'.$capture->get('createManager.user.id').'/orders/'
                    .$capture->get('createOrder2.id').'/bid',
                'auth' => 'guest',
                'includes' => ['appraisers'],
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ],
                'body' => [
                    'amount' => 999.99,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'comments' => 'ezpz',
                    'appraisers' => [
                        $capture->get('createAppraiser.id'),
                        $capture->get('createAppraiser1.id')
                    ]
                ]
            ],
            'response' => [
                'body' => [
                    'amount' => 999.99,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'comments' => 'ezpz',
                    'appraisers' => [
                        new Dynamic(function ($appraiser) use ($capture) {
                            return $appraiser['id'] === $capture->get('createAppraiser.id');
                        }),
                        new Dynamic(function ($appraiser) use ($capture) {
                            return $appraiser['id'] === $capture->get('createAppraiser1.id');
                        }),
                    ]
                ]
            ],
            'push' => [
                'body' => [
                    'type' => 'order',
                    'event' => 'submit-bid',
                    'order' => $capture->get('createOrder2.id')
                ],
                'single' => true
            ]
        ];
    },

    'getBid' => function (Runtime $runtime) use ($estimatedCompletionDate) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/orders/'
                    .$capture->get('createOrder2.id').'/bid',
                'auth' => 'guest',
                'includes' => ['appraisers'],
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ],
            ],
            'response' => [
                'body' => [
                    'amount' => 999.99,
                    'estimatedCompletionDate' => $estimatedCompletionDate,
                    'comments' => 'ezpz',
                    'appraisers' => [
                        new Dynamic(function ($appraiser) use ($capture) {
                            return $appraiser['id'] === $capture->get('createAppraiser.id');
                        }),
                        new Dynamic(function ($appraiser) use ($capture) {
                            return $appraiser['id'] === $capture->get('createAppraiser1.id');
                        }),
                    ]
                ]
            ]
        ];
    },

    'acceptBid:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /customers/'.$runtime->getSession('customer')->get('user.id')
                    .'/orders/'.$capture->get('createOrder2.id').'/award',
                'auth' => 'customer',
            ]
        ];
    },

    'getOrder' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/orders/'
                    .$capture->get('createOrder2.id'),
                'auth' => 'guest',
                'includes' => ['subAssignees'],
                'headers' => [
                    'Token' => $capture->get('signinManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createOrder2.id'),
                    'fileNumber' => new Dynamic(Dynamic::STRING),
                    'subAssignees' => [
                        new Dynamic(function ($appraiser) use ($capture) {
                            return $appraiser['id'] === $capture->get('createAppraiser.id');
                        }),
                        new Dynamic(function ($appraiser) use ($capture) {
                            return $appraiser['id'] === $capture->get('createAppraiser1.id');
                        }),
                    ]
                ]
            ]
        ];
    }
];
