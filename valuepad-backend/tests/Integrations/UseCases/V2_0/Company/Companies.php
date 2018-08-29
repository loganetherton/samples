<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Response;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Staff;

$newAppraiser = [
    'username' => 'appraisercheckcompanytaxid',
    'password' => 'asdfauhsfdgvi324e',
];

$newAppraiser1 = [
    'username' => 'qwdqw@test.org',
    'password' => 'passwordasxasx',
];

$firstCompany = [
    'name' => 'An appraisal company',
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
    'taxId' => '11-4578457',
    'type' => CompanyType::INDIVIDUAL_TAX_ID,
    'ach' => [
        'bankName' => 'sadfasdfwe',
        'accountNumber' => '11122221122',
        'accountType' => AchAccountType::CHECKING,
        'routing' => '123221232'
    ]
];

$includes = [
    'firstName',
    'lastName',
    'email',
    'phone',
    'fax',
    'address1',
    'city',
    'zip',
    'assignmentZip',
    'state',
    'taxId',
    'type',
    'w9',
    'staff',
    'eo',
    'ach'
];

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

    'createW91:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createEoDocument1:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],

    'createW92:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createEoDocument2:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.txt'
            ]
        ]
    ],

    'createAppraiser:init' => function(Runtime $runtime) use ($newAppraiser) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $newAppraiser['username'],
            'password' => $newAppraiser['password'],
            'w9' => [
                'id' => $capture->get('createW91.id'),
                'token' => $capture->get('createW91.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'TX'
                ],
            ],
            'eo' => [
                'document' => [
                    'id' => $capture->get('createEoDocument1.id'),
                    'token' => $capture->get('createEoDocument1.token')
                ]
            ]
        ]);

        for ($i = 1; $i <=7; $i++){
            $data['eo']['question'.$i] = false;
        }

        return [
            'request' => [
                'url' => 'POST /appraisers',
                'body' => $data
            ]
        ];
    },

    'createAppraiser1:init' => function (Runtime $runtime) use ($newAppraiser1) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $newAppraiser1['username'],
            'password' => $newAppraiser1['password'],
            'w9' => [
                'id' => $capture->get('createW92.id'),
                'token' => $capture->get('createW92.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'AL'
                ],
            ],
            'eo' => [
                'document' => [
                    'id' => $capture->get('createEoDocument2.id'),
                    'token' => $capture->get('createEoDocument2.token')
                ]
            ]
        ]);

        for ($i = 1; $i <=7; $i++){
            $data['eo']['question'.$i] = false;
        }

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
            'body' => $newAppraiser
        ]
    ],

    'signinAppraiser1:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $newAppraiser1
        ]
    ],

    'tryCreateCompany1' => [
        'request' => [
            'url' => 'POST /companies',
            'body' => [
                'ach' => [
                    'bankName' => 'xxx',
                    'accountNumber' => '123456789',
                    'accountType' => AchAccountType::CHECKING,
                    'routing' => '123456789'
                ]
            ]
        ],
        'response' => [
            'errors' => [
                'name' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'firstName' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'lastName' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'email' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'phone' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'address1' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'city' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'zip' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'assignmentZip' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'state' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'w9' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'taxId' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'type' => [
                    'identifier' => 'required',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ]
            ]
        ]
    ],

    'tryCreateCompany2' => [
       'request' => [
            'url' => 'POST /companies',
            'body' => [
                'name' => '',
                'firstName' => '',
                'lastName' => '',
                'email' => '',
                'phone' => '',
                'fax' => '',
                'address1' => '',
                'address2' => '',
                'city' => '',
                'zip' => '',
                'assignmentZip' => '',
                'state' => '',
                'w9' => [
                    'id' => -1,
                    'token' => 'xxx',
                ],
                'taxId' => '',
                'type' => CompanyType::INDIVIDUAL_TAX_ID,
                'eo' => [
                    'claimAmount' => -1,
                    'aggregateAmount' => -1,
                    'expiresAt' => (new DateTime('- 1 day'))->format(DateTime::ATOM),
                    'carrier' => '',
                    'deductible' => -1,
                    'document' => [
                        'id' => -1,
                        'token' => 'xxx',
                    ]
                ],
                'ach' => [
                    'bankName' => '',
                    'accountType' => AchAccountType::CHECKING,
                    'accountNumber' => '',
                    'routing' => ''
                ]
            ]
       ],
       'response' => [
            'errors' => [
                'name' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'firstName' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'lastName' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'email' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'phone' => [
                    'identifier' => 'format',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'fax' => [
                    'identifier' => 'format',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'address1' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'address2' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'city' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'zip' => [
                    'identifier' => 'format',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'assignmentZip' => [
                    'identifier' => 'format',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'state' => [
                    'identifier' => 'length',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'w9' => [
                    'identifier' => 'exists',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'taxId' => [
                    'identifier' => 'format',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'eo.claimAmount' => [
                    'identifier' => 'greater',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'eo.aggregateAmount' => [
                    'identifier' => 'greater',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'eo.expiresAt' => [
                    'identifier' => 'greater',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'eo.carrier' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'eo.deductible' => [
                    'identifier' => 'greater',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'eo.document' => [
                    'identifier' => 'exists',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'ach.bankName' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'ach.accountNumber' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ],
                'ach.routing' => [
                    'identifier' => 'empty',
                    'message' => new Dynamic(Dynamic::STRING),
                    'extra' => []
                ]
            ]
        ]
    ],

    'tryCreateCompany3' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'body' => [
                    'name' => str_random(256),
                    'firstName' => str_random(51),
                    'lastName' => str_random(51),
                    'email' => str_random(256),
                    'phone' => '123456789',
                    'address1' => 'Address line 1',
                    'city' => str_random(10),
                    'zip' => 'ddf32332xsd',
                    'assignmentZip' => '232xx23dfrg',
                    'state' => 'XX',
                    'w9' => [
                        'id' => $capture->get('createW9.id'),
                        'token' => 'INVALIDTOKEN'
                    ],
                    'taxId' => '98s4df',
                    'eo' => [
                        'document' => [
                            'id' => $capture->get('createEoDocument.id'),
                            'token' => 'INVALIDTOKEN'
                        ]
                    ],
                    'ach' => [
                        'accountNumber' => '23746293874623987483129',
                        'routing' => '928376492346293874623978'
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'name' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'firstName' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'lastName' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'email' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'phone' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'zip' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'assignmentZip' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'state' => [
                        'identifier' => 'exists',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'w9' => [
                        'identifier' => 'permissions',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'taxId' => [
                        'identifier' => 'format',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'type' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'eo.document' => [
                        'identifier' => 'permissions',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'eo.claimAmount' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'eo.aggregateAmount' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'eo.expiresAt' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'ach.accountNumber' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'ach.routing' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'ach.bankName' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'ach.accountType' => [
                        'identifier' => 'required',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'createCompany1' => function (Runtime $runtime) use ($firstCompany, $includes) {
        $capture = $runtime->getCapture();
        $eoDate = (new DateTime('+1 month'))->format('c');
        $session = $runtime->getSession('appraiser');

        return [
            'request' => [
                'url' => 'POST /companies',
                'includes' => $includes,
                'body' => array_merge(
                    $firstCompany,
                    [
                        'w9' => [
                            'id' => $capture->get('createW9.id'),
                            'token' => $capture->get('createW9.token')
                        ],
                        'eo' => [
                            'document' => [
                                'id' => $capture->get('createEoDocument.id'),
                                'token' => $capture->get('createEoDocument.token')
                            ],
                            'claimAmount' => 21.22,
                            'aggregateAmount' => 33.11,
                            'deductible' => 3.22,
                            'expiresAt' => $eoDate,
                            'carrier' => 'asdfasdf'
                        ]
                    ]
                )
            ],
            'response' => [
                'body' => array_replace_recursive(
                    $firstCompany,
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'state' => ['code' => 'AL', 'name' => 'Alabama'],
                        'w9' => $capture->get('createW9'),
                        'eo' => [
                            'document' => $capture->get('createEoDocument'),
                            'claimAmount' => 21.22,
                            'aggregateAmount' => 33.11,
                            'deductible' => 3.22,
                            'expiresAt' => $eoDate,
                            'carrier' => 'asdfasdf',
                            'id' => new Dynamic(Dynamic::INT)
                        ],
                        'staff' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'email' => $session->get('user.email'),
                            'phone' => $session->get('user.phone'),
                            'user' => new Dynamic(function($user){
                                return is_array($user);
                            }),
                            'isAdmin' => true,
                            'isManager' => false,
                            'isRfpManager' => false
                        ],
                        'ach' => [
                            'accountNumber' => '1122',
                            'routing' => '1232'
                        ]
                    ]
                )
            ]
        ];
    },

    'validateTaxIdTaken' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/tax-id/'.$capture->get('createCompany1.taxId'),
                // Response should only contain ID and Name
                'includes' => ['email']
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createCompany1.id'),
                    'name' => $capture->get('createCompany1.name'),
                ]
            ]
        ];
    },

    'validateTaxIdAvailable' => [
        'request' => [
            'url' => 'GET /companies/tax-id/99-8888888'
        ],
        'response' => [
            'status' => Response::HTTP_NOT_FOUND
        ]
    ],

    'tryCreateCompany4' => [
        'request' => [
            'url' => 'POST /companies',
            'body' => $firstCompany
        ],
        'response' => [
            'status' => Response::HTTP_BAD_REQUEST
        ]
    ],

    'tryCreateCompany5' => function (Runtime $runtime) use ($firstCompany) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => array_merge(
                    $firstCompany,
                    ['w9' => ['id' => $capture->get('createW91.id'), 'token' => $capture->get('createW91.token')]]
                )
            ],
            'response' => [
                'errors' => [
                    'taxId' => [
                        'identifier' => 'unique',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ]
                ]
            ]
        ];
    },

    'createCompany2' => function (Runtime $runtime) use ($firstCompany) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => array_merge(
                    $firstCompany,
                    [
                        'name' => 'New appraisal company',
                        'taxId' => '12-1111111',
                        'w9' => ['id' => $capture->get('createW91.id'), 'token' => $capture->get('createW91.token')],
                        'email' => 'different@email.com',
                        'type' => CompanyType::OTHER,
                        'otherType' => 'Other company type',
                    ]
                )
            ]
        ];
    },

    'createCompany3' => function (Runtime $runtime) use ($firstCompany) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ],
                'body' => array_merge(
                    $firstCompany,
                    [
                        'name' => 'And another one',
                        'taxId' => '12-3332457',
                        'w9' => ['id' => $capture->get('createW92.id'), 'token' => $capture->get('createW92.token')],
                        'email' => 'mah@boi.com'
                    ]
                )
            ]
        ];
    },

    'addAppraiserToSecondCompany:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'raw' => function (EntityManagerInterface $entityManager) use ($capture, $runtime) {

                /**
                 * @var Appraiser $appraiser
                 */
                $appraiser = $entityManager->getReference(Appraiser::class, $runtime->getSession('appraiser')->get('user.id'));

                /**
                 * @var Branch $branch
                 */
                $branch = $entityManager->getRepository(Branch::class)->findOneBy([
                    'isDefault' => true,
                    'company' => $capture->get('createCompany2.id')
                ]);

                $staff = new Staff();
                $staff->setCompany($branch->getCompany());
                $staff->setBranch($branch);
                $staff->setUser($appraiser);

                $entityManager->persist($staff);
                $entityManager->flush();
            }
        ];
    },

    'tryEditWrongCompany1' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany3.id'),
                'body' => [
                    'name' => 'lol im posing myself as someone else'
                ],
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },

    'tryEditCompanyWithIncorrectRole' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany2.id'),
                'body' => [
                    'name' => 'No permissions to administer company'
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },

    'editCompany' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany1.id'),
                'body' => [
                    'name' => 'New Company Name'
                ]
            ]
        ];
    },

    'getAllAfterEdit' => function (Runtime $runtime) {
        $session = $runtime->getSession('appraiser');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$session->get('user.id').'/companies',
                'includes' => ['staff']
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'name' => 'New Company Name',
                        'staff' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'email' => $session->get('user.email'),
                            'phone' => $session->get('user.phone'),
                            'user' => new Dynamic(function($user){
                                return is_array($user);
                            }),
                            'isAdmin' => true,
                            'isManager' => false,
                            'isRfpManager' => false
                        ]
                    ],
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'name' => $capture->get('createCompany2.name'),
                        'staff' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'email' => $session->get('user.email'),
                            'phone' => $session->get('user.phone'),
                            'user' => new Dynamic(function($user){
                                return is_array($user);
                            }),
                            'isAdmin' => false,
                            'isManager' => false,
                            'isRfpManager' => false
                        ]
                    ]
                ]
            ]
        ];
    }
];
