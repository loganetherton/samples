<?php

use Ascope\QA\Support\Filters\ArrayFieldsFilter;
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

$customer = [
    'username' => uniqid('customer'),
    'password' => 'password'
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
                    'name' => 'Glam',
                    'firstName' => 'pa',
                    'lastName' => 'ayy',
                    'email' => 'interview@injustice.com',
                    'phone' => '(333) 123-2897',
                    'fax' => '(333) 123-8237',
                    'address1' => 'xxx9as8',
                    'city' => 'ORAAAAAAAA!',
                    'zip' => '11124',
                    'assignmentZip' => '47854',
                    'state' => 'AL',
                    'taxId' => '31-3387321',
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

    'createCustomer:init' => [
        'request' => [
            'url' => 'POST /customers',
            'body' => array_merge($customer, ['name' => $customer['username']])
        ]
    ],

    'associateManagerWithCustomer:init' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();
        $customerSession = $runtime->getSession('customer');

        return [
            'raw' => function (EntityManagerInterface $em) use ($capture, $customerSession) {
                $manager = $em->find(Manager::class, $capture->get('createManager.user.id'));
                $customer = $em->find(Customer::class, $customerSession->get('user.id'));
                $customer1 = $em->find(Customer::class, $capture->get('createCustomer.id'));

                $customer->addManager($manager);
                $customer1->addManager($manager);

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

    'getCustomers' => function (Runtime $runtime) {
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /managers/'.$capture->get('createManager.user.id').'/customers',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('loginManager.token')
                ]
            ],
            'response' => [
                'body' => [
                    ['id' => $runtime->getSession('customer')->get('user.id')],
                    ['id' => $capture->get('createCustomer.id')]
                ],
                'filter' => new ArrayFieldsFilter(['id'], true)
            ]
        ];
    }
];
