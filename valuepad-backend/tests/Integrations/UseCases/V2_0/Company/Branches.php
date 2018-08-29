<?php

use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Response;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Company\Entities\Branch;

$appraiser = [
    'username' => 'datappraisertho',
    'password' => 'hihihaha'
];

$appraiser1 = [
    'username' => 'apsdofijea',
    'password' => 'asdfiuosahdfo'
];

$branch = [
    'name' => 'Branching Branch',
    // This should be allowed because it's the company's tax ID
    'taxId' => '97-0332132',
    'address1' => 'wooooooooooooooooo',
    'city' => 'Abilene',
    'state' => 'TX',
    'zip' => '87545',
    'assignmentZip' => '15648',
];

$branchEo = [
    'claimAmount' => 220.00,
    'aggregateAmount' => 11.1,
    'deductible' => 2.3,
    'expiresAt' => (new DateTime('+1 month'))->format('c'),
    'carrier' => 'asdfg'
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

    'createW91:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
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

    'createW93:init' => [
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
                    'state' => 'WI'
                ]
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
            'w9' => $capture->get('createW92'),
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'MT'
                ]
            ],
            'eo' => [
                'document' => $capture->get('createEoDocument1')
            ]
        ]);

        for ($i =  1; $i <= 7; $i++) {
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
            'body' => $appraiser
        ]
    ],

    'signinAppraiser1:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $appraiser1
        ]
    ],

    'createCompany:init' => function (Runtime $runtime) use ($branch) {
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
                    'firstName' => 'ayyy',
                    'lastName' => 'lmao',
                    'email' => 'fg@dfgz.co.jp',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'Ooooooo',
                    'city' => 'Uranus',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => $branch['taxId'],
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'w9' => [
                        'id' => $capture->get('createW91.id'),
                        'token' => $capture->get('createW91.token')
                    ]
                ]
            ]
        ];
    },

    'createCompany1:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser1.token')
                ],
                'body' => [
                    'name' => 'asoidufhosadyuf',
                    'firstName' => 'asdfv',
                    'lastName' => 'sddd',
                    'email' => 'asdiofuhsad@ddd.com',
                    'phone' => '(333) 232-3333',
                    'address1' => 'sdfasdf',
                    'city' => 'asdfsaf',
                    'zip' => '32322',
                    'assignmentZip' => '33322',
                    'state' => 'LA',
                    'taxId' => '22-3329192',
                    'type' => CompanyType::INDIVIDUAL_TAX_ID,
                    'w9' => [
                        'id' => $capture->get('createW93.id'),
                        'token' => $capture->get('createW93.token')
                    ]
                ]
            ]
        ];
    },

    'tryCreate' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
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
                ]
            ],
            'response' => [
                'errors' => [
                    'name' => [
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
                    'state' => [
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
                ]
            ]
        ];
    },

    'tryCreate1' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => '',
                    'address1' => '',
                    'city' => '',
                    'state' => '',
                    'zip' => '',
                    'assignmentZip' => '',
                    'eo' => [
                        'document' => [
                            'id' => $capture->get('createEoDocument1.id'),
                            'token' => 'INVALIDTOKEN'
                        ]
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
                    'address1' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'city' => [
                        'identifier' => 'empty',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'state' => [
                        'identifier' => 'length',
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
                    ]
                ]
            ]
        ];
    },

    'tryCreate2' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => str_random(256),
                    'address1' => 'aaaaaa',
                    'city' => str_random(10),
                    'state' => 'XX',
                    'zip' => 'asdfasf323',
                    'assignmentZip' => 'asdfjsuier534'
                ]
            ],
            'response' => [
                'errors' => [
                    'name' => [
                        'identifier' => 'length',
                        'message' => new Dynamic(Dynamic::STRING),
                        'extra' => []
                    ],
                    'state' => [
                        'identifier' => 'exists',
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
                ]
            ]
        ];
    },

    'create' => function (Runtime $runtime) use ($branch) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => $branch
            ],
            'response' => [
                'body' => [
                    'id' => new Dynamic(Dynamic::INT),
                    'name' => $branch['name']
                ]
            ]
        ];
    },

    'tryEditWithTaxIdTaken' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$capture->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    // Should fail because it's used by the second company
                    'taxId' => '22-3329192'
                ]
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

    'tryEditWithIncompleteEo' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$capture->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'eo' => [
                        'carrier' => 'wolololololo'
                    ]
                ]
            ],
            'response' => [
                'errors' => [
                    'eo.document' => [
                        'identifier' => 'required',
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
                    ]
                ]
            ]
        ];
    },

    'edit' => function (Runtime $runtime) use ($branchEo) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$capture->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'body' => [
                    'name' => 'New branch name',
                    'taxId' => '00-1112221',
                    'eo' => array_merge($branchEo, [
                        'document' => [
                            'id' => $capture->get('createEoDocument1.id'),
                            'token' => $capture->get('createEoDocument1.token'),
                        ]
                    ])
                ]
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ]
        ];
    },

    'getAll' => function (Runtime $runtime) use ($branch) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id')
                    .'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ],
                'includes' => ['taxId', 'eo']
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'name' => 'Default Branch',
                        'taxId' => $branch['taxId'],
                        'eo' => null
                    ],
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'name' => 'New branch name',
                        'taxId' => '00-1112221',
                        'eo' => new Dynamic(function($v){
                            return is_array($v);
                        })
                    ]
                ]
            ]
        ];
    },

    'moveToNewBranch:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'raw' => function (EntityManagerInterface $entityManager) use ($capture) {

                /**
                 * @var Staff $staff
                 */
                $staff = $entityManager->getRepository(Staff::class)->findOneBy([
                    'company' => $capture->get('createCompany.id'),
                    'user' => $capture->get('createAppraiser.id')
                ]);

                /**
                 * @var Branch $branch
                 */
                $branch = $entityManager->getReference(Branch::class, $capture->get('create.id'));
                $staff->setBranch($branch);

                $entityManager->flush();
            }
        ];
    },

    'tryDeleteDefault' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $default = array_filter($capture->get('getAll'), function ($branch) {
            return $branch['name'] === 'Default Branch';
        })[0];

        return [
            'request' => [
                'url' => 'DELETE /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$default['id'],
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'status' => Response::HTTP_BAD_REQUEST
            ]
        ];
    },

    'tryDeleteNonEmpty' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'DELETE /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$capture->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'status' => Response::HTTP_BAD_REQUEST
            ]
        ];
    },

    'moveToDefaultBranch:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $default = array_filter($capture->get('getAll'), function ($branch) {
            return $branch['name'] === 'Default Branch';
        })[0];

        return [
            'raw' => function (EntityManagerInterface $entityManager) use ($capture, $default) {

                /**
                 * @var Staff $staff
                 */
                $staff = $entityManager->getRepository(Staff::class)->findOneBy([
                    'company' => $capture->get('createCompany.id'),
                    'user' => $capture->get('createAppraiser.id')
                ]);

                /**
                 * @var Branch $branch
                 */
                $branch = $entityManager->getReference(Branch::class, $default['id']);
                $staff->setBranch($branch);

                $entityManager->flush();
            }
        ];
    },

    'delete' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'DELETE /companies/'.$capture->get('createCompany.id')
                    .'/branches/'.$capture->get('create.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ]
        ];
    },

    'getAllAfterDelete' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id')
                    .'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAppraiser.token')
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'name' => 'Default Branch'
                    ]
                ]
            ]
        ];
    }
];
