<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use Ascope\QA\Support\Filters\ArrayFieldsFilter;

$appraiser = uniqid('appraiser');
$appraiser2 = uniqid('appraiser');
$appraiser3 = uniqid('appraiser');
$appraiser4 = uniqid('appraiser');
$appraiser5 = uniqid('appraiser');
$manager1 = uniqid('manager');
$manager2 = uniqid('manager');

return [
    'createW9:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createW9_2:init' => [
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

    'createAppraiser:init' => function(Runtime $runtime) use ($appraiser){

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

    'signinAppraiser' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $appraiser,
                'password' => 'password'
            ]
        ]
    ],

    'createAppraiser2:init' => function(Runtime $runtime) use ($appraiser2){

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

    'signinAppraiser2' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $appraiser2,
                'password' => 'password'
            ]
        ]
    ],

    'createAppraiser3:init' => function(Runtime $runtime) use ($appraiser3){

        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser3,
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

    'createAppraiser4:init' => function(Runtime $runtime) use ($appraiser4){

        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser4,
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


    'createAppraiser5:init' => function(Runtime $runtime) use ($appraiser5){

        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser5,
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

    'createCompany:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'includes' => ['email'],
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'The World Appraisal Company',
                    'firstName' => 'ayyy',
                    'lastName' => 'lmao',
                    'email' => 'kaori@kaoru.co.jp',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'Ooooooo',
                    'city' => 'Uranus',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => '09-4534567',
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
                    'taxId' => '09-4534567',
                    'address1' => 'wooooooooooooooooo',
                    'city' => 'Abilene',
                    'state' => 'TX',
                    'zip' => '87545',
                    'assignmentZip' => '15648',
                    'eo' => [
                        'claimAmount' => 220.00,
                        'aggregateAmount' => 11.1,
                        'deductible' => 2.3,
                        'expiresAt' => (new DateTime('+1 month'))->format('c'),
                        'carrier' => 'asdfg',
                        'document' => [
                            'id' => $capture->get('createEoDocument.id'),
                            'token' => $capture->get('createEoDocument.token')
                        ]
                    ]
                ]
            ]
        ];
    },
    'createCompany2:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'includes' => ['email'],
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser2.token')
                ],
                'body' => [
                    'name' => 'The World 2nd Appraisal Company',
                    'firstName' => 'ayyy',
                    'lastName' => 'lmao',
                    'email' => 'kaori@kaoru.co.jp',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'Ooooooo',
                    'city' => 'Uranus',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => '09-5534567',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'ach' => [
                        'bankName' => 'sadfasdfwe',
                        'accountNumber' => '11122221122',
                        'accountType' => AchAccountType::CHECKING,
                        'routing' => '123221232'
                    ],
                    'w9' => ['id' => $capture->get('createW9_2.id'), 'token' => $capture->get('createW9_2.token')],
                    'otherType' => 'Other company type',
                ]
            ],
        ];
    },
    'createBranch2:init' => function (Runtime $runtime)  {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany2.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser2.token')
                ],
                'body' => [
                    'name' => 'Branching Branch',
                    'taxId' => '09-5534567',
                    'address1' => 'wooooooooooooooooo',
                    'city' => 'Abilene',
                    'state' => 'TX',
                    'zip' => '87545',
                    'assignmentZip' => '15648',
                    'eo' => [
                        'claimAmount' => 220.00,
                        'aggregateAmount' => 11.1,
                        'deductible' => 2.3,
                        'expiresAt' => (new DateTime('+1 month'))->format('c'),
                        'carrier' => 'asdfg',
                        'document' => [
                            'id' => $capture->get('createEoDocument.id'),
                            'token' => $capture->get('createEoDocument.token')
                        ]
                    ]
                ]
            ]
        ];
    },

    'addAppraisersToCompanies:init' => function(Runtime $runtime){

        return [
            'raw' => function(EntityManagerInterface $entityManager) use ($runtime){

                $result = [
                    'staff' => []
                ];

                $staff = new Staff();

                $staff->setCompany($entityManager->getReference(Company::class, $runtime->getCapture()->get('createCompany.id')));
                $staff->setBranch($entityManager->getReference(Branch::class, $runtime->getCapture()->get('createBranch.id')));
                $staff->setUser($entityManager->getReference(Appraiser::class, $runtime->getCapture()->get('createAppraiser3.id')));

                $entityManager->persist($staff);

                $entityManager->flush();

                $result['staff'][1] = $staff->getId();

                $staff = new Staff();

                $staff->setCompany($entityManager->getReference(Company::class, $runtime->getCapture()->get('createCompany.id')));
                $staff->setBranch($entityManager->getReference(Branch::class, $runtime->getCapture()->get('createBranch.id')));
                $staff->setUser($entityManager->getReference(Appraiser::class, $runtime->getCapture()->get('createAppraiser4.id')));

                $entityManager->persist($staff);

                $entityManager->flush();

                $result['staff'][2] = $staff->getId();

                $staff = new Staff();

                $staff->setCompany($entityManager->getReference(Company::class, $runtime->getCapture()->get('createCompany2.id')));
                $staff->setBranch($entityManager->getReference(Branch::class, $runtime->getCapture()->get('createBranch2.id')));
                $staff->setUser($entityManager->getReference(Appraiser::class, $runtime->getCapture()->get('createAppraiser5.id')));

                $entityManager->persist($staff);

                $entityManager->flush();

                $result['staff'][3] = $staff->getId();


                ($runtime->getCapture())::add('addAppraisersToCompanies', $result);
            }
        ];
    },

    'createManager1:init' => function(Runtime $runtime) use ($manager1){
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'includes' => ['branch', 'user.phone'],
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'user' => [
                        'username' => $manager1,
                        'password' => 'secret',
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'email' => 'testytest@gmail.com',
                        'phone' => '(999) 242-2211',
                    ],
                    'branch' => $runtime->getCapture()->get('createBranch.id')
                ]
            ],
        ];
    },

    'createManager2:init' => function(Runtime $runtime) use ($manager2){
        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'includes' => ['branch', 'user.phone'],
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'user' => [
                        'username' => $manager2,
                        'password' => 'secret',
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'email' => 'testytest@gmail.com',
                        'phone' => '(999) 242-2211',
                    ],
                    'branch' => $runtime->getCapture()->get('createBranch.id')
                ]
            ],
        ];
    },

    'tryAddPermissionsWithManager' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createManager1.id').'/permissions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'data' => [
                        $runtime->getCapture()->get('createManager2.id'),
                        $runtime->getCapture()->get('addAppraisersToCompanies.staff.1'),
                        $runtime->getCapture()->get('addAppraisersToCompanies.staff.2')
                    ]
                ]
            ],
            'response' => [
                'status' => 400
            ]
        ];
    },


    'tryAddPermissions' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createManager1.id').'/permissions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'data' => [
                        $runtime->getCapture()->get('addAppraisersToCompanies.staff.1'),
                        $runtime->getCapture()->get('addAppraisersToCompanies.staff.3')
                    ]
                ]
            ],
            'response' => [
                'status' => 400
            ]
        ];
    },

    'addPermissions' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PUT /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createManager1.id').'/permissions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ],
                'body' => [
                    'data' => [
                        $runtime->getCapture()->get('createManager1.id'),
                        $runtime->getCapture()->get('addAppraisersToCompanies.staff.1'),
                        $runtime->getCapture()->get('addAppraisersToCompanies.staff.2')
                    ]
                ]
            ]
        ];
    },

    'getPermissions' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'GET /companies/'.$runtime->getCapture()->get('createCompany.id')
                    .'/staff/'.$runtime->getCapture()->get('createManager1.id').'/permissions',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $runtime->getCapture()->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'id' =>  $runtime->getCapture()->get('addAppraisersToCompanies.staff.1'),
                    ],
                    [
                        'id' => $runtime->getCapture()->get('addAppraisersToCompanies.staff.2')
                    ]
                ],
                'filter' => new ArrayFieldsFilter(['id'], true)
            ]
        ];
    },
];
