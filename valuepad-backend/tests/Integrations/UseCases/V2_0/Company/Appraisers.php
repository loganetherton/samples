<?php

use Ascope\QA\Support\Filters\ArrayFieldsFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\BusinessType;
use ValuePad\Core\Appraiser\Enums\CommercialExpertise;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Branch;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Permission;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$owner = [
    'username' => uniqid('appraiser'),
    'password' => uniqid(),
];

$appraiser = [
    'username' => uniqid('appraiser'),
    'password' => uniqid(),
];

$update =  [
    'availability' => [
        'isOnVacation' => true,
        'from' => (new DateTime('-10 days'))->format(DateTime::ATOM),
        'to' => (new DateTime('+ 10 days'))->format(DateTime::ATOM),
        'message' => 'Testing...'
    ],
    'companyName' => 'Test Company',
    'businessTypes' => [BusinessType::WOMEN_OWNED_BUSINESS],
    'companyType' => CompanyType::PARTNERSHIP,
    'otherCompanyType' => 'updated custom company type',
    'firstName' => 'Will',
    'lastName' => 'Smith',
    'languages' => ['fra', 'deu'],
    'address1' => '111 Holloway ave',
    'address2' => '222 Holloway ave',
    'city' => 'Oakland',
    'state' => 'FL',
    'zip' => '94102',
    'assignmentAddress1' => '666 Holloway ave',
    'assignmentAddress2' => '13 Holloway ave',
    'assignmentCity' => 'New York',
    'assignmentState' => 'NY',
    'assignmentZip' => '20001',
    'phone' => '(555) 777-9999',
    'cell' => '(666) 777-0003',
    'fax' => '(234) 111-5555',
    'taxIdentificationNumber' => '677-32-9878',
    'qualifications' => [
        'yearsLicensed' => 4,
        'certifiedAt' => [
            'month' => 10,
            'year' => 2002
        ],
        'vaQualified' => false,
        'fhaQualified' => false,
        'relocationQualified' => false,
        'usdaQualified' => true,
        'coopQualified' => false,
        'jumboQualified' => true,
        'newConstructionQualified' => false,

        'newConstructionExperienceInYears' => 45,
        'numberOfNewConstructionCompleted' => 22,
        'isNewConstructionCourseCompleted' => false,
        'isFamiliarWithFullScopeInNewConstruction' => true,

        'loan203KQualified' => false,
        'manufacturedHomeQualified' => false,
        'reoQualified' => false,
        'deskReviewQualified' => false,
        'fieldReviewQualified' => false,
        'envCapable' => false,
        'commercialQualified' => false,
        'commercialExpertise' => [CommercialExpertise::OTHER, CommercialExpertise::MULTI_FAMILY],
        'otherCommercialExpertise' => 'some stuff',
    ],
    'eo' => [
        'claimAmount' => 444.09,
        'aggregateAmount' => 2,
        'expiresAt' => (new DateTime('+4 month'))->format('c'),
        'carrier' => 'different',
        'deductible' => 331,
    ],
    'signature' => 'Will Smith',
    'signedAt' => (new DateTime('-9 days'))->format(DateTime::ATOM)
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

    'createOwner:init' => function (Runtime $runtime) use ($owner) {
        $capture = $runtime->getCapture();

        $data = AppraisersFixture::get([
            'username' => $owner['username'],
            'password' => $owner['password'],
            'w9' => $capture->get('createW9'),
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'CA'
                ]
            ],
            'eo' => [
                'document' => $capture->get('createEoDocument')
            ],
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

    'loginOwner:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $owner
        ]
    ],

    'createCompany:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ],
                'body' => [
                    'name' => 'Edit Appraisers Profile',
                    'firstName' => 'pa',
                    'lastName' => 'ayy',
                    'email' => 'shiny@sparkly.com',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'xxx9as8',
                    'city' => 'Muda Da!',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => '87-3164879',
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
            'w9' => $capture->get('createW91'),
            'qualifications' => [
                'primaryLicense' => [
                    'number' => 'dummy',
                    'state' => 'TX'
                ]
            ],
            'eo' => [
                'document' => $capture->get('createEoDocument1')
            ],
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

    'addAppraiserToCompany:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'raw' => function (EntityManagerInterface $em) use ($capture) {
                $company = $em->find(Company::class, $capture->get('createCompany.id'));
                $appraiser = $em->find(Appraiser::class, $capture->get('createAppraiser.id'));
                $owner = $em->getRepository(Staff::class)->findOneBy(['user' => $capture->get('createOwner.id')]);
                $branch = $em->getRepository(Branch::class)->findOneBy(['company' => $company->getId()]);

                $owner->setManager(true);

                $staff = new Staff();
                $staff->setCompany($company);
                $staff->setEmail($appraiser->getEmail());
                $staff->setPhone($appraiser->getPhone());
                $staff->setBranch($branch);
                $staff->setUser($appraiser);

                $permission = new Permission();
                $permission->setManager($owner);
                $permission->setAppraiser($staff);

                // This is used for the filter test later
                $appraiser->setAssignmentZip('45243');

                $em->persist($staff);
                $em->persist($permission);
                $em->flush();
            }
        ];
    },

    'loginAppraiser:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $appraiser
        ]
    ],

    'getAppraiserProfileWithNoPermission' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/appraisers/'.$capture->get('createOwner.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginAppraiser.token')
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },

    'getAppraiserProfile' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/appraisers/'.$capture->get('createAppraiser.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $capture->get('createAppraiser.id')
                ],
                'filter' => new ItemFieldsFilter(['id'], true)
            ]
        ];
    },

    'updateAppraiserProfileWithNoPermission' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany.id').'/appraisers/'.$capture->get('createOwner.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginAppraiser.token')
                ],
                'body' => [
                    'firstName' => 'HEHEHEHE'
                ]
            ],
            'response' => [
                'status' => Response::FORBIDDEN
            ]
        ];
    },

    'updateAppraiserProfile' => function (Runtime $runtime) use ($update) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /companies/'.$capture->get('createCompany.id').'/appraisers/'.$capture->get('createAppraiser.id'),
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ],
                'body' => $update
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT,
                'debug' => true
            ]
        ];
    },

    'getAppraiserProfileAfterUpdate' => function (Runtime $runtime) use ($update) {
        $capture = $runtime->getCapture();
        $response = $update;

        $response['languages'] = [
            ['code' => 'deu', 'name' => 'German'],
            ['code' => 'fra', 'name' => 'French'],
        ];

        $response['state'] = ['code' => 'FL', 'name' => 'Florida'];
        $response['assignmentState'] = ['code' => 'NY', 'name' => 'New York'];

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/appraisers/'.$capture->get('createAppraiser.id'),
                'auth' => 'guest',
                'includes' => array_keys(array_dot($update)),
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ]
            ],
            'response' => [
                'body' => $response,
                'filter' => new ItemFieldsFilter(array_keys(array_dot($update)), true)
            ]
        ];
    },

    'getAppraisers' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/appraisers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ]
            ],
            'response' => [
                'body' => [
                    ['id' => $capture->get('createOwner.id')],
                    ['id' => $capture->get('createAppraiser.id')]
                ],
                'filter' => new ArrayFieldsFilter(['id'], true)
            ]
        ];
    },

    'getAppraiserStaff:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/staff',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ]
            ]
        ];
    },

    'createOrder:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        $order = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        $order['property']['zip'] = '45243';

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$runtime->getSession('customer')->get('user.id').'/companies/'
                    .$capture->get('createCompany.id').'/staff/'
                    .$capture->get('getAppraiserStaff.1.id').'/orders',
                'auth' => 'customer',
                'body' => $order
            ]
        ];
    },

    'getAppraisersWithNoOrderFilter' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/appraisers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ],
                'parameters' => [
                    'distance' => 1250
                ]
            ],
            'response' => [
                'status' => Response::HTTP_BAD_REQUEST
            ]
        ];
    },

    'getAppraisersWithNoDistanceFilter' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/appraisers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ],
                'parameters' => [
                    'orderId' => $capture->get('createOrder.id')
                ]
            ],
            'response' => [
                'status' => Response::HTTP_BAD_REQUEST
            ]
        ];
    },

    'getAppraisersWithFilter' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/appraisers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ],
                'parameters' => [
                    'orderId' => $capture->get('createOrder.id'),
                    'distance' => 1250
                ]
            ],
            'response' => [
                'body' => [
                    ['id' => $capture->get('createAppraiser.id')]
                ],
                'filter' => new ArrayFieldsFilter(['id'], true)
            ]
        ];
    }
];
