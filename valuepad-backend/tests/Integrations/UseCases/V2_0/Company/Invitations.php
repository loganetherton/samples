<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Invitation;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$appraiser = [
    'username' => str_random(15),
    'password' => str_random(15)
];

$appraiser1 = [
    'username' => str_random(15),
    'password' => str_random(15)
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

    'createAppraiser:init' => function (Runtime $runtime) use ($appraiser) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser['username'],
            'password' => $appraiser['password'],
            'w9' => $capture->get('createW9'),
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'SD',
                ]
            ],
            'eo' => [
                'document' => $capture->get('createEoDocument'),
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
    'createAppraiser1:init' => function (Runtime $runtime) use ($appraiser1) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser1['username'],
            'password' => $appraiser1['password'],
            'w9' => $capture->get('createW91'),
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'VT',
                ]
            ],
            'eo' => [
                'document' => $capture->get('createEoDocument1'),
            ]
        ]);

        for ($i = 1; $i <= 7; $i++) {
            $data['eo']['question'.$i] = false;
        }

        return [
            'request' => [
                'url' => 'POST /appraisers',
                'includes' => ['qualifications'],
                'body' => $data
            ]
        ];
    },
    'signinAppraiser:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $appraiser
        ]
    ],
    'signinAppraiser1:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $appraiser1
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
                    'name' => 'An appraisal company',
                    'firstName' => 'Company',
                    'lastName' => 'Owner',
                    'email' => 'dat@email.com',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'Ooooooo',
                    'city' => 'Hahahaha',
                    'zip' => '99999',
                    'assignmentZip' => '11111',
                    'state' => 'CA',
                    'taxId' => '88-8878157',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'ach' => [
                        'bankName' => 'sadfasdfwe',
                        'accountNumber' => '11122221122',
                        'accountType' => AchAccountType::CHECKING,
                        'routing' => '123221232'
                    ],
                    'w9' => $capture->get('createW9'),
                    'eo' => [
                        'document' => $capture->get('createEoDocument'),
                        'claimAmount' => 21.22,
                        'aggregateAmount' => 33.11,
                        'deductible' => 3.22,
                        'expiresAt' => (new DateTime('+1 month'))->format('c'),
                        'carrier' => 'asdfasdf'
                    ]
                ],
            ]
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
    'getAscProfile:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /asc',
                'parameters' => [
                    'search' => [
                        'licenseNumber' => $capture->get('createAppraiser1.qualifications.primaryLicense.number')
                    ],
                    'filter' => [
                        'licenseState' => $capture->get('createAppraiser1.qualifications.primaryLicense.state.code')
                    ]
                ]
            ]
        ];
    },
    'createAsAdmin' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $branch = $capture->get('getBranches.0');

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches/'
                    .$branch['id'].'/invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'ascAppraiser' => $capture->get('getAscProfile.0.id'),
                    'email' => 'dummy@mail.gov',
                    'phone' => '(111) 232-2322'
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'ascAppraiser' => $capture->get('getAscProfile.0'),
                    'branch' => $branch,
                    'email' => 'dummy@mail.gov',
                    'phone' => '(111) 232-2322',
                    'requirements' => []
                ]
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'dat@email.com' => $capture->get('createCompany.name')
                        ],
                        'to' => [
                            'dummy@mail.gov' => null
                        ],
                        'subject' => 'You\'ve Been Invited to Join '.$capture->get('createCompany.name'),
                        'contents' => new Dynamic(function ($value) use ($capture) {
                            $data = [
                                'firstName' => $capture->get('getAscProfile.0.firstName'),
                                'companyName' => $capture->get('createCompany.name')
                            ];

                            return str_contains($value, array_values($data));
                        })
                    ]
                ]
            ]
        ];
    },
    'acceptInvitation:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'raw' => function (EntityManagerInterface $em) use ($capture) {
                /**
                 * @var Invitation $invitation
                 */
                $invitation = $em->find(Invitation::class, $capture->get('createAsAdmin.id'));

                $staff = new Staff();
                $staff->setBranch($invitation->getBranch());
                $staff->setCompany($invitation->getBranch()->getCompany());
                $staff->setUser($invitation->getAscAppraiser()->getAppraiser());

                $em->persist($staff);
                $em->remove($invitation);

                $em->flush();
            }
        ];
    },
    'createWithoutPermission' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $branch = $capture->get('getBranches.0.id');

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches/'
                    .$branch.'/invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ],
                'body' => [
                    'ascAppraiser' => 31,
                    'email' => 'nonregistered@appraiser.com',
                    'phone' => '(322) 111-2222',
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },
    'markAppraiserAsManager:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'raw' => function (EntityManagerInterface $em) use ($capture) {
                /**
                 * @var Staff $staff
                 */
                $staff = $em->getRepository(Staff::class)->findOneBy([
                    'user' => $capture->get('createAppraiser1.id'),
                    'company' => $capture->get('createCompany.id')
                ]);

                $staff->setManager(true);

                $em->flush();
            }
        ];
    },
    'createAsManager' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $branch = $capture->get('getBranches.0');

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches/'
                    .$branch['id'].'/invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ],
                'body' => [
                    'ascAppraiser' => 31,
                    'email' => 'nonregistered@appraiser.com',
                    'phone' => '(322) 111-2222',
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'branch' => $branch,
                    'ascAppraiser' => [
                        'id' => 31
                    ],
                    'email' => 'nonregistered@appraiser.com',
                    'phone' => '(322) 111-2222'
                ],
                'filter' => new ItemFieldsFilter([
                    'id', 'branch', 'ascAppraiser.id', 'email', 'phone'
                ], true)
            ],
            'emails' => [
                'body' => [
                    [
                        'from' => [
                            'dat@email.com' => $capture->get('createCompany.name')
                        ],
                        'to' => [
                            'nonregistered@appraiser.com' => null
                        ],
                        'subject' => 'You\'ve Been Invited to Join '.$capture->get('createCompany.name'),
                        'contents' => new Dynamic(function ($value) use ($capture) {
                            $data = [
                                'firstName' => 'first31',
                                'companyName' => $capture->get('createCompany.name'),
                            ];

                            return str_contains($value, array_values($data));
                        })
                    ]
                ]
            ]
        ];
    },
    'getAllPending' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $branch = $capture->get('getBranches.0');

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/branches/'
                    .$branch['id'].'/invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'branch' => $branch,
                    'ascAppraiser' => [
                        'id' => 31
                    ],
                    'email' => 'nonregistered@appraiser.com',
                    'phone' => '(322) 111-2222',
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function() { return true; }),
                    new ItemFieldsFilter([
                        'id', 'branch', 'ascAppraiser.id', 'email', 'phone'
                    ], true)
                ])
            ]
        ];
    }
];
