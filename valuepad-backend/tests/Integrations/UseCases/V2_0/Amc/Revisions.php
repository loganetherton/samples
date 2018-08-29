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

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/revisions',
				'auth' => 'customer',
				'body' => [
					'checklist' => ['Item #1', 'Item #2', 'Item #3']
				]
			]
		];
	},
	'create2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/revisions',
				'auth' => 'customer',
				'body' => [
					'message' => 'Test Message'
				]
			]
		];
	},
    'getOne' => function(Runtime $runtime){
        $session = $runtime->getSession('amc');
        $capture = $runtime->getCapture();

        return [
            'request' => [
                'url' => 'GET /amcs/'.$session->get('user.id').'/revisions/'.$capture->get('create1.id'),
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
                'url' => 'GET /amcs/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id').'/revisions/'.$capture->get('create1.id'),
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
					.$capture->get('createOrder.id').'/revisions',
				'auth' => 'amc',
			],
			'response' => [
				'body' => [$capture->get('create1'), $capture->get('create2')]
			]
		];
	}
];