<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

return [
	'createOrder:init' => function(Runtime $runtime) use ($dueDate){
		$customerSession = $runtime->getSession('customer');
		$amcSession = $runtime->getSession('amc');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['jobType'] = 3;

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/amcs/'
					.$amcSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},
	'accept:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'POST /amcs/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/accept',
				'auth' => 'amc'
			]
		];
	},
	'scheduleInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/schedule-inspection',
				'auth' => 'amc',
				'body' => [
					'scheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			]
		];
	},
	'completeInspection:init' => function(Runtime $runtime) use ($estimatedCompletionDate, $completedAt){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/complete-inspection',
				'auth' => 'amc',
				'body' => [
					'completedAt' => $completedAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			]
		];
	},
	'createPdf:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'complete:init' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/document',
				'auth' => 'amc',
				'body' => [
					'primary' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			]
		];
	},

	'create1:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$closedDate1 = (new DateTime('+1 days'))->format(DateTime::ATOM);
		$closedDate2 = (new DateTime('+2 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment 1',
					'comparables' => [
						[
							'address' => 'Address 1',
							'salesPrice' => 1.1,
							'closedDate' => $closedDate1,
							'livingArea' => 'Some area to live 1',
							'siteSize' => 'Large 1',
							'actualAge' => 'old 1',
							'distanceToSubject' => 'Long 1',
							'sourceData' => 'Some source 1',
							'comment' => 'Some comment 1'
						],
						[
							'address' => 'Address 2',
							'salesPrice' => 2.2,
							'closedDate' => $closedDate2,
							'livingArea' => 'Some area to live 2',
							'siteSize' => 'Large 2',
							'actualAge' => 'old 2',
							'distanceToSubject' => 'Long 2',
							'sourceData' => 'Some source 2',
							'comment' => 'Some comment 2'
						]
					]
				]
			],
		];
	},
	'create2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		$closedDate1 = (new DateTime('+1 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'customer',
				'body' => [
					'comment' => 'Test comment 2',
					'comparables' => [
						[
							'address' => 'Address 3',
							'salesPrice' => 1.1,
							'closedDate' => $closedDate1,
							'livingArea' => 'Some area to live 3',
							'siteSize' => 'Large 3',
							'actualAge' => 'old 3',
							'distanceToSubject' => 'Long 3',
							'sourceData' => 'Some source 3',
							'comment' => 'Some comment 3'
						]
					]
				]
			],
		];
	},
    'getOne' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/reconsiderations/'.$capture->get('create1.id'),
                'auth' => 'amc',
            ],
            'response' => [
                'body' => $capture->get('create1')
            ]
        ];
    },
    'getOneByOrder' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/reconsiderations/'.$capture->get('create1.id'),
                'auth' => 'amc',
            ],
            'response' => [
                'body' => $capture->get('create1')
            ]
        ];
    },
	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/reconsiderations',
				'auth' => 'amc',
			],
			'response' => [
				'body' => [$capture->get('create1'), $capture->get('create2')]
			]
		];
	}
];