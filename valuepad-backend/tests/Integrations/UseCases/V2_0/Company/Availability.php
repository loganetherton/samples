<?php

use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

$owner = [
    'username' => uniqid('appraiser'),
    'password' => 'password'
];

$manager = [
    'username' => uniqid('manager'),
    'password' => 'password'
];

$from = (new DateTime())->format(DateTime::ATOM);
$to = (new DateTime('+1 months'))->format(DateTime::ATOM);

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
                    'name' => 'Avail Cust',
                    'firstName' => 'pa',
                    'lastName' => 'ayy',
                    'email' => 'journey@critics.com',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'xxx9as8',
                    'city' => 'ORAAAAAAAA!',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => '31-2154874',
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

    'getBranches:init' => function (Runtime $runtime)  {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /companies/'.$capture->get('createCompany.id').'/branches',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginOwner.token')
                ]
            ]
        ];
    },

    'createManager:init' => function (Runtime $runtime) use ($manager) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'POST /companies/'.$runtime->getCapture()->get('createCompany.id').'/managers',
                'auth' => 'guest',
                'includes' => ['branch', 'user.phone'],
                'headers' => [
                    'Token' => $runtime->getCapture()->get('loginOwner.token')
                ],
                'body' => [
                    'user' => [
                        'username' => $manager['username'],
                        'password' => $manager['password'],
                        'firstName' => 'Man',
                        'lastName' => 'Ager',
                        'email' => 's9d8f4df52de@gmail.com',
                        'phone' => '(999) 242-2211',
                    ],
                    'branch' => $runtime->getCapture()->get('getBranches.0.id'),
                    'notifyUser' => false,
                    'isManager' => true,
                    'isRfpManager' => true,
                    'isAdmin' => true
                ]
            ],
        ];
    },

    'associateManagerWithCustomer:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $customerSession = $runtime->getSession('customer');

        return [
            'raw' => function (EntityManagerInterface $em) use ($capture, $customerSession) {
                $manager = $em->find(Manager::class, $capture->get('createManager.user.id'));
                $customer = $em->find(Customer::class, $customerSession->get('user.id'));

                $customer->addManager($manager);

                $em->flush();
            }
        ];
    },

    'loginManager:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => $manager
        ]
    ],

    'setOnVacationForCustomer' => function (Runtime $runtime) use ($from, $to) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /managers/'.$capture->get('createManager.user.id').'/customers/'
                    .$runtime->getSession('customer')->get('user.id').'/availability',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginManager.token')
                ],
                'body' => [
                    'isOnVacation' => true,
                    'from' => $from,
                    'to' => $to,
                    'message' => 'ecksdee'
                ]
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'manager',
                        'event' => 'update',
                        'manager' => $capture->get('createManager.user.id')
                    ]
                ]
            ]
        ];
    },
    'getAvailabilityForCustomer' => function (Runtime $runtime) use ($from, $to) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/customers/'
                    .$runtime->getSession('customer')->get('user.id').'/availability',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    'isOnVacation' => true,
                    'from' => $from,
                    'to' => $to,
                    'message' => 'ecksdee'
                ]
            ]
        ];
    },
    'setAvailableForCustomer' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PATCH /managers/'.$capture->get('createManager.user.id').'/customers/'
                    .$runtime->getSession('customer')->get('user.id').'/availability',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginManager.token')
                ],
                'body' => [
                    'isOnVacation' => false
                ]
            ],
            'response' => [
                'status' => Response::HTTP_NO_CONTENT
            ],
            'push' => [
                'body' => [
                    [
                        'type' => 'manager',
                        'event' => 'update',
                        'manager' => $capture->get('createManager.user.id')
                    ]
                ]
            ]
        ];
    },
    'getAvailabilityForCustomerAfterSetAvailable' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/customers/'
                    .$runtime->getSession('customer')->get('user.id').'/availability',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    'isOnVacation' => false
                ],
                'filter' => new ItemFieldsFilter(['isOnVacation'], true)
            ],
        ];
    }
];
