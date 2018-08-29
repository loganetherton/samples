<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Staff;

$firstCompany = [
    'name' => 'herecomedatcompany',
    'firstName' => 'who',
    'lastName' => 'dis',
    'email' => 'iranai@hatsuiku.com',
    'phone' => '(123) 122-7878',
    'address1' => 'meeeeeeeeeeeeeeeeee',
    'city' => 'FFRfrfrfrf',
    'zip' => '11147',
    'assignmentZip' => '99987',
    'state' => 'MA',
    'taxId' => '97-2223331',
    'type' => CompanyType::INDIVIDUAL_TAX_ID,
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
    'staff'
];

$newAppraiser = [
    'username' => 'dddddddddd@test.org',
    'password' => 'password',
];

$newAppraiser1 = [
    'username' => 'dfcasdsdc@test.org',
    'password' => 'passwordasxasx',
];

$newAppraiser2 = [
    'username' => 'walaoweee@sdfsd.com',
    'password' => 'haizzzzzz',
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

        for ($i = 1; $i <=7; $i++){
            $data['eo']['question'.$i] = false;
        }

        return [
            'request' => [
                'url' => 'POST /appraisers',
                'includes' => ['email', 'phone'],
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
                'id' => $capture->get('createW91.id'),
                'token' => $capture->get('createW91.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'AL'
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

    'createAppraiser2:init' => function (Runtime $runtime) use ($newAppraiser2) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $newAppraiser2['username'],
            'password' => $newAppraiser2['password'],
            'w9' => [
                'id' => $capture->get('createW92.id'),
                'token' => $capture->get('createW92.token')
            ],
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'ND'
                ]
            ],
            'eo' => [
                'document' => [
                    'id' => $capture->get('createEoDocument2.id'),
                    'token' => $capture->get('createEoDocument2.token')
                ]
            ]
        ]);

        for ($i = 1; $i <= 7; $i++) {
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

    'signinAppraiser2:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $newAppraiser2
        ]
    ],

    'createCompany:init' => function (Runtime $runtime) use ($firstCompany, $includes) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'includes' => $includes,
                'body' => array_merge(
                    $firstCompany,
                    [
                        'w9' => [
                            'id' => $capture->get('createW9.id'),
                            'token' => $capture->get('createW9.token')
                        ]
                    ]
                )
            ]
        ];
    },

    'createCompany1:init' => function (Runtime $runtime) use ($firstCompany) {
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
                        'name' => 'New appraisal company',
                        'taxId' => '12-3311111',
                        'w9' => ['id' => $capture->get('createW91.id'), 'token' => $capture->get('createW91.token')],
                        'email' => 'different@email.com',
                        'type' => CompanyType::OTHER,
                        'otherType' => 'Other company type',
                    ]
                )
            ]
        ];
    },

    'createCompany2:init' => function (Runtime $runtime) use ($firstCompany) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser2.token')
                ],
                'body' => array_merge(
                    $firstCompany,
                    [
                        'name' => 'And another one',
                        'taxId' => '12-9312457',
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
            'raw' => function (EntityManagerInterface $entityManager) use ($capture) {
                /**
                 * @var Appraiser $appraiser
                 */
                $appraiser = $entityManager->getReference(Appraiser::class, $capture->get('createAppraiser.id'));

                /**
                 * @var Branch $branch
                 */
                $branch = $entityManager->getRepository(Branch::class)->findOneBy([
                    'isDefault' => true,
                    'company' => $capture->get('createCompany1.id')
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

    'getAll' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'includes' => ['staff']
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'name' => $capture->get('createCompany.name'),
                        'staff' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'user' => new Dynamic(function($user){
                                return is_array($user);
                            }),
                            'email' => $capture->get('createAppraiser.email'),
                            'phone' => $capture->get('createAppraiser.phone'),
                            'isAdmin' => true,
                            'isManager' => false,
                            'isRfpManager' => false
                        ]
                    ],
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'name' => $capture->get('createCompany1.name'),
                        'staff' => [
                            'id' => new Dynamic(Dynamic::INT),
                            'user' => new Dynamic(function($user){
                                return is_array($user);
                            }),
                            'email' => $capture->get('createAppraiser.email'),
                            'phone' => $capture->get('createAppraiser.phone'),
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
