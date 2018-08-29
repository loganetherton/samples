<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\User\Enums\Status;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Amc\Entities\Invoice;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Amc\Entities\Item;
use ValuePad\Core\Amc\Entities\Amc;
use Ascope\QA\Support\Response;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Payment\Enums\Means;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Payment\Enums\AccountType;

$amc = uniqid('amc');

return [
    'createAmc:init' => [
        'request' => [
            'url' => 'POST /amcs',
            'auth' => 'guest',
            'body' => [
                'username' => $amc,
                'password' => 'password',
                'email' => 'bestamc@ever.org',
                'companyName' => 'Best AMC Ever!',
                'address1' => '123 Wall Str.',
                'address2' => '124B Wall Str.',
                'city' => 'New York',
                'zip' => '44211',
                'state' => 'NY',
                'lenders' => 'VMX, TTT, abc',
                'phone' => '(423) 553-1211',
                'fax' => '(423) 553-1212'
            ]
        ],
    ],

    'approveAmc:init' => function(Runtime $runtime){
        return [
            'request' => [
                'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc.id'),
                'auth' => 'admin',
                'body' => [
                    'status' => Status::APPROVED
                ]
            ]
        ];
    },

    'signinAmc:init' => [
        'request' => [
            'url' => 'POST /sessions',
            'body' => [
                'username' => $amc,
                'password' => 'password'
            ]
        ]
    ],

    'createPdf:init' => [
        'request' => [
            'url' => 'POST /documents',
            'files' => [
                'document' => __DIR__.'/test.pdf'
            ]
        ]
    ],

    'createOrder:init' => function(Runtime $runtime){
        $customerSession = $runtime->getSession('customer');
        $amc = $runtime->getCapture()->get('createAmc');

        $data = OrdersFixture::get($runtime->getHelper(), [
            'client' => 1,
            'clientDisplayedOnReport' => 2
        ]);

        $data['isTechFeePaid'] = false;
        $data['techFee'] = 100;

        return [
            'request' => [
                'url' => 'POST /customers/'
                    .$customerSession->get('user.id').'/amcs/'
                    .$amc['id'].'/orders',
                'auth' => 'customer',
                'body' => $data
            ]
        ];
    },

    'generateInvoices:init' => function(Runtime $runtime){

        $capture = $runtime->getCapture();

        return [
            'raw' => function(EntityManagerInterface $em) use ($capture) {

                /**
                 * @var Document $document
                 */
                $document = $em->getReference(Document::class, $capture->get('createPdf.id'));

                /**
                 * @var Amc $amc
                 */
                $amc = $em->getReference(Amc::class, $capture->get('createAmc.id'));

                //-----------------------------------------------------------

                $invoice = new Invoice();
                $invoice->setAmount(100);


                $invoice->setDocument($document);

                $invoice->setAmc($amc);

                $invoice->setFrom(new DateTime('-20 days'));
                $invoice->setTo(new DateTime('-2 days'));
                $invoice->setCreatedAt(new DateTime('2016-01-02 12:22:11'));

                $em->persist($invoice);

                $em->flush();

                $items = [];

                /**
                 * @var Order $order
                 */
                $order = $em->find(Order::class, $capture->get('createOrder.id'));

                $item = new Item();
                $item->setAmount(50);
                $item->setAddress('123 Market Str., San Francisco, CA 94132');
                $item->setBorrowerName('James Brown');
                $item->setCompletedAt(new DateTime('-1 days'));
                $item->setOrderedAt(new DateTime('-9 days'));
                $item->setFileNumber('XXXCCCVVV');
                $item->setLoanNumber('YYYEEEAAA');
                $item->setJobType('URAR(422) - Expensive Stuff');
                $item->setInvoice($invoice);
                $item->setOrder($order);

                $em->persist($item);

                $em->flush();

                $invoice->setItems($items);

                $em->flush();

                // --------------------------------------------------------

                $invoice = new Invoice();
                $invoice->setAmount(422.99);
                $invoice->setDocument($document);
                $invoice->setAmc($amc);
                $invoice->setFrom(new DateTime('-20 days'));
                $invoice->setTo(new DateTime('-2 days'));
                $invoice->setCreatedAt(new DateTime('2016-01-03 12:22:11'));
                $invoice->setPaid(true);

                $em->persist($invoice);
                $em->flush();
            }
        ];
    },

    'getAll' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/invoices',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'amount' => 100,
                        'isPaid' => false,
                        'from' => new Dynamic(Dynamic::DATETIME),
                        'to' => new Dynamic(Dynamic::DATETIME),
                        'createdAt' => new Dynamic(Dynamic::DATETIME),
                        'document' => $runtime->getCapture()->get('createPdf'),
                        'items' => [
                            [
                                'id' => new Dynamic(Dynamic::INT),
                                'amount' => 50,
                                'fileNumber' => 'XXXCCCVVV',
                                'loanNumber' => 'YYYEEEAAA',
                                'jobType' => 'URAR(422) - Expensive Stuff',
                                'borrowerName' => 'James Brown',
                                'address' => '123 Market Str., San Francisco, CA 94132',
                                'orderedAt' => new Dynamic(Dynamic::DATETIME),
                                'completedAt' => new Dynamic(Dynamic::DATETIME),
                            ]
                        ]
                    ],
                    [
                        'id' => new Dynamic(Dynamic::INT),
                        'amount' => 422.99,
                        'isPaid' => true,
                        'from' => new Dynamic(Dynamic::DATETIME),
                        'to' => new Dynamic(Dynamic::DATETIME),
                        'createdAt' => new Dynamic(Dynamic::DATETIME),
                        'document' => $runtime->getCapture()->get('createPdf'),
                        'items' => []
                    ]
                ]
            ]
        ];
    },

    'getAllPaid' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/invoices',
                'parameters' => [
                    'filter' => [
                        'isPaid' => 'true'
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'assert' => function(Response $response) {
                    $data = $response->getBody();

                    if (!$data){
                        return false;
                    }

                    foreach ($data as $row){
                        if ($row['isPaid'] === false){
                            return false;
                        }
                    }

                    return true;
                }
            ]
        ];
    },


    'getOrderedDesc' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/invoices',
                'parameters' => [
                    'orderBy' => 'createdAt:desc'
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'createdAt' => (new DateTime('2016-01-03 12:22:11'))->format(DateTime::ATOM)
                ],
                'filter' => new CompositeFilter([
                    new FirstFilter(function(){ return true; }),
                    new ItemFieldsFilter(['createdAt'], true)
                ])
            ]
        ];
    },

    'getAllNotPaid:init' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/invoices',
                'parameters' => [
                    'filter' => [
                        'isPaid' => 'false'
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ]
        ];
    },

    'getOrderUnpaid' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/orders/'.$order['id'],
                'auth' => 'guest',
                'includes' => ['isTechFeePaid'],
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $order['id'],
                    'fileNumber' => $order['fileNumber'],
                    'isTechFeePaid' => false
                ]
            ]
        ];
    },

    'tryPayInvoice' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $amc = $capture->get('createAmc');
        $invoice = $capture->get('getAllNotPaid.0');
        $session = $capture->get('signinAmc');

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/invoices/'.$invoice['id'].'/pay',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'means' => Means::BANK_ACCOUNT
                ]
            ],
            'response' => [
                'status' => Response::HTTP_BAD_REQUEST
            ]
        ];
    },

    'createBankAccount:init' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'PUT /amcs/'.$capture->get('createAmc.id').'/payment/bank-account',
                'auth' => 'guest',
                'headers' => [
                    'Token' => $capture->get('signinAmc.token')
                ],
                'body' => [
                    'accountType' => AccountType::CHECKING,
                    'routingNumber' => '021000021',
                    'accountNumber' => '9900000002',
                    'nameOnAccount' => 'John Connor',
                    'bankName' => 'World Best Bank'
                ]
            ]
        ];
    },

    'payInvoice' => function(Runtime $runtime){
        $capture = $runtime->getCapture();

        $amc = $capture->get('createAmc');
        $invoice = $capture->get('getAllNotPaid.0');
        $session = $capture->get('signinAmc');

        return [
            'request' => [
                'url' => 'POST /amcs/'.$amc['id'].'/invoices/'.$invoice['id'].'/pay',
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ],
                'body' => [
                    'means' => Means::BANK_ACCOUNT
                ]
            ]
        ];
    },

    'getAllNotPaid' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/invoices',
                'parameters' => [
                    'filter' => [
                        'isPaid' => 'false'
                    ]
                ],
                'auth' => 'guest',
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => []
            ]
        ];
    },

    'getOrderPaid' => function(Runtime $runtime){
        $session = $runtime->getCapture()->get('signinAmc');
        $amc = $runtime->getCapture()->get('createAmc');
        $order = $runtime->getCapture()->get('createOrder');

        return [
            'request' => [
                'url' => 'GET /amcs/'.$amc['id'].'/orders/'.$order['id'],
                'auth' => 'guest',
                'includes' => ['isTechFeePaid'],
                'headers' => [
                    'token' => $session['token']
                ]
            ],
            'response' => [
                'body' => [
                    'id' => $order['id'],
                    'fileNumber' => $order['fileNumber'],
                    'isTechFeePaid' => true
                ]
            ]
        ];
    }
];
