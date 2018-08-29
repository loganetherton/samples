<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Entities\Ach;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Invitation\Enums\Requirement;
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
                    'state' => 'TX'
                ],
            ],
            'eo' => [
                'document' => $capture->get('createEoDocument')
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
    'createAppraiser1:init' => function (Runtime $runtime) use ($appraiser1) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $appraiser1['username'],
            'password' => $appraiser1['password'],
            'w9' => $capture->get('createW91'),
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'TX'
                ],
            ],
            'eo' => [
                'document' => $capture->get('createEoDocument1')
            ]
        ]);

        for ($i = 1; $i <=7; $i++){
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
                    'name' => 'Haaaaaaaahahahahaha',
                    'firstName' => 'Wow',
                    'lastName' => 'So Random',
                    'email' => 'sugusugu@sugu.com',
                    'phone' => '(123) 999-7878',
                    'address1' => 'Vroom vroom',
                    'city' => 'Cranky City',
                    'zip' => '54781',
                    'assignmentZip' => '31548',
                    'state' => 'CA',
                    'taxId' => '11-6457849',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'w9' => $capture->get('createW9')
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
    'getAsc:init' => function (Runtime $runtime) {
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
    'createInvitation:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $branch = $capture->get('getBranches.0.id');

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$branch.'/invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'ascAppraiser' => $capture->get('getAsc.0.id'),
                    'phone' => '(121) 213-9238',
                    'email' => 'mail@it.com',
                ]
            ]
        ];
    },
    'getAll' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$capture->get('createAppraiser1.id').'/company-invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ]
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'branch' => [
                        'id' => $capture->get('getBranches.0.id')
                    ],
                    'ascAppraiser' => [
                        'id' => $capture->get('getAsc.0.id')
                    ],
                    'phone' => '(121) 213-9238',
                    'email' => 'mail@it.com',
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function () { return true; }),
                    new ItemFieldsFilter(['id', 'branch.id', 'ascAppraiser.id', 'phone', 'email'], true)
                ])
            ]
        ];
    },
    'declineInvitation' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser1.id')
                    .'/company-invitations/'.$capture->get('createInvitation.id').'/decline',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ]
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ]
        ];
    },
    'createInvitation1:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $branch = $capture->get('getBranches.0.id');

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$branch.'/invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'ascAppraiser' => $capture->get('getAsc.0.id'),
                    'phone' => '(121) 213-9238',
                    'email' => 'mail@it.com',
                    'requirements' => [Requirement::ACH, Requirement::RESUME, Requirement::SAMPLE_REPORTS],
                ]
            ]
        ];
    },
    'tryAcceptInvitationWithoutFulfillingRequirements' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser1.id')
                    .'/company-invitations/'.$capture->get('createInvitation1.id').'/accept',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ]
            ],
            'response' => [
                'status' => Response::HTTP_BAD_REQUEST
            ],
            'raw' => function (EntityManagerInterface $em) use ($capture) {
                $appraiser = $em->find(Appraiser::class, $capture->get('createAppraiser1.id'));

                $ach = new Ach();
                $ach->setBankName(str_random(10));
                $ach->setAccountType(new AchAccountType(AchAccountType::SAVING));
                $ach->setRouting(str_random(9));
                $ach->setAccountNumber(str_random(11));
                $em->persist($ach);

                $appraiser->setAch($ach);

                $document = $em->find(Document::class, $capture->get('createW91.id'));

                $appraiser->getQualifications()->setResume($document);

                $appraiser->setSampleReports([$document]);

                $em->flush();
            }
        ];
    },

    'acceptInvitation' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /appraisers/'.$capture->get('createAppraiser1.id')
                    .'/company-invitations/'.$capture->get('createInvitation1.id').'/accept',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ]
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ]
        ];
    },
    'getAllAfterAccept' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /appraisers/'.$capture->get('createAppraiser1.id').'/company-invitations',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ]
            ],
            'response' => [
                'body' => []
            ]
        ];
    }
];
