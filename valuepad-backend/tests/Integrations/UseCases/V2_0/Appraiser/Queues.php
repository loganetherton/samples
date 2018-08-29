<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

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

	'createAppraiser:init' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => 'appraisertestsummary',
			'password' => 'password',
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

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'includes' => ['qualifications'],
				'body' => $data
			]
		];
	},

	'getAscAppraiser:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /asc',
				'parameters' => [
					'search' => [
						'licenseNumber' => $capture->get('createAppraiser.qualifications.primaryLicense.number')
					],
					'filter' => [
						'licenseState' => $capture->get('createAppraiser.qualifications.primaryLicense.state.code')
					]
				]
			]
		];
	},

	'invite:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => $capture->get('getAscAppraiser.0.id')
				],
				'auth' => 'customer',
			]
		];
	},

	'loginAppraiser:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'appraisertestsummary',
				'password' => 'password'
			]
		]
	],

	'acceptInvitation:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
		];
	},
	'createOrder1:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createBidRequest:init' => function(Runtime $runtime){
		$data = OrdersFixture::getAsBidRequest($runtime->getHelper(), ['client' => 1, 'clientDisplayedOnReport' => 2]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder2:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder3:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder4:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder5:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder6:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder7:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder8:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'createOrder9:init' => function(Runtime $runtime){
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/appraisers/'.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},

	'acceptOrder2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id')
					.'/orders/'.$capture->get('createOrder2.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			]
		];
	},

	'inspectionScheduledOrder3:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder3.id').'/workflow/inspection-scheduled',
				'auth' => 'customer',
				'body' => [
					'scheduledAt' => (new DateTime('+2 days'))->format(DateTime::ATOM),
					'estimatedCompletionDate' => (new DateTime('+10 days'))->format(DateTime::ATOM),
				]
			]
		];
	},

	'inspectionCompletedOrder4:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder4.id').'/workflow/inspection-completed',
				'auth' => 'customer',
				'body' => [
					'completedAt' => (new DateTime('-2 days'))->format(DateTime::ATOM),
					'estimatedCompletionDate' => (new DateTime('+10 days'))->format(DateTime::ATOM),
				]
			]
		];
	},

	'putOnHoldOrder5:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder5.id').'/workflow/on-hold',
				'auth' => 'customer',
				'body' => [
					'explanation' => 'test'
				]
			]
		];
	},

	'completeOrder6:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder6.id').'/workflow/completed',
				'auth' => 'customer'
			]
		];
	},

	'lateOrder7:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder7.id').'/workflow/late',
				'auth' => 'customer'
			]
		];
	},

	'lateOrder8:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder8.id').'/workflow/revision-pending',
				'auth' => 'customer'
			]
		];
	},

	'lateOrder9:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder9.id').'/workflow/revision-in-review',
				'auth' => 'customer'
			]
		];
	},

	'getNew' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/new',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 2
			]
		];
	},

	'getAccepted' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/accepted',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 1
			]
		];
	},

	'getInspected' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/inspected',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 1
			]
		];
	},


	'getScheduled' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/scheduled',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 1
			]
		];
	},


	'getOnHold' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/on-hold',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 1
			]
		];
	},

	'getLate' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/late',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 1
			]
		];
	},

	'getCompleted' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/completed',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 1
			]
		];
	},

	'getRevision' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/revision',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 2
			]
		];
	},

	'getDue' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/due',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 3
			]
		];
	},

	'getOpen' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/open',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 9
			]
		];
	},

	'getAll' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/all',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'total' => 10
			]
		];
	},

	'get' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/queues/counters',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('loginAppraiser.token')
				]
			],
			'response' => [
				'body' => [
					'new' => 2,
					'accepted' => 1,
					'inspected' => 1,
					'scheduled' => 1,
					'onHold' => 1,
					'late' => 1,
					'readyForReview' => 0,
					'completed' => 1,
					'revision' => 2,
					'due' => 3,
					'open' => 9,
					'all' => 10
				]
			]
		];
	}
];
