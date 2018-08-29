<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$newAppraiser = [
    'username' => str_random(15),
    'password' => str_random(15),
];

$newAppraiser1 = [
    'username' => str_random(15),
    'password' => str_random(15),
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
                'includes' => ['email', 'phone'],
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
                    'name' => 'etttttttttttttttt',
                    'firstName' => 'XXXXXXXXXXXXXXXXX',
                    'lastName' => 'wferfffff',
                    'email' => 'gimmedat@email.com',
                    'phone' => '(123) 122-9999',
                    'address1' => 'where dis',
                    'city' => 'burp',
                    'zip' => '99999',
                    'assignmentZip' => '11111',
                    'state' => 'TX',
                    'taxId' => '27-1245331',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'w9' => [
                        'id' => $capture->get('createW9.id'),
                        'token' => $capture->get('createW9.token')
                    ]
                ]
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
                $appraiser = $entityManager->getReference(Appraiser::class, $capture->get('createAppraiser1.id'));

                /**
                 * @var Branch $branch
                 */
                $branch = $entityManager->getRepository(Branch::class)->findOneBy([
                    'isDefault' => true,
                    'company' => $capture->get('createCompany.id')
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

    'getBranches:init' => function (Runtime $runtime) {
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

    'getAll' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/staff',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'includes' => ['branch', 'user.phone']
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'branch' => $capture->get('getBranches.0'),
                        'user' => $capture->get('createAppraiser'),
                        'email' => $capture->get('createAppraiser.email'),
                        'phone' => $capture->get('createAppraiser.phone'),
                        'isAdmin' => true,
                        'isManager' => false,
                        'isRfpManager' => false
                    ],
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'branch' => $capture->get('getBranches.0'),
                        'user' => $capture->get('createAppraiser1'),
                        'email' => $capture->get('createAppraiser1.email'),
                        'phone' => $capture->get('createAppraiser1.phone'),
                        'isAdmin' => false,
                        'isManager' => false,
                        'isRfpManager' => false
                    ]
                ]
            ]
        ];
    }
];
