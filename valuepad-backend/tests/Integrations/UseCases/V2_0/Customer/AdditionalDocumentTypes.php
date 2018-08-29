<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use Illuminate\Http\Response;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;

return [
	'tryCreate' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types',
				'auth' => 'customer',
				'body' => [

				]
			],
			'response' => [
				'errors' => [
					'title' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'create' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test type'
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'title' => 'Test type'
				]
			]
		];
	},

	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => $capture->get('create.id'),
					'title' => 'Test type'
				],
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create.id');
				})
			]
		];
	},

	'update' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'title' => 'Test type updated'
				]
			]
		];
	},
	'getAllUpdated' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => $capture->get('create.id'),
					'title' => 'Test type updated'
				],
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create.id');
				})
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types/'.$capture->get('create.id'),
				'auth' => 'customer',
			]
		];
	},

	'tryUpdate' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'title' => 'Test type updated'
				]
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
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
	'create2' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types',
				'auth' => 'customer',
				'body' => [
					'title' => 'Test type'
				]
			]
		];
	},

	'createOrder:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},
	'accept:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/accept',
			]
		];
	},
	'addAdditionalDocument:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/additional-documents',
				'body' => [
					'type' => $capture->get('create2.id'),
					'document' => [
						'id' => $capture->get('createPdf.id'),
						'token' => $capture->get('createPdf.token')
					]
				]
			]
		];
	},
	'tryDeleteWithUsedByDocument' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id')
					.'/settings/additional-documents/types/'.$capture->get('create2.id'),
				'auth' => 'customer',
			],
			'response' => [
				'status' => Response::HTTP_BAD_REQUEST
			]
		];
	},

];