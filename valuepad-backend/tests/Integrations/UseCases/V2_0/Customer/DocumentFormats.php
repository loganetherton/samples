<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use Illuminate\Http\Response;

return [
	'validate' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/documents/formats',
				'auth' => 'customer',
				'body' => [
					'jobType' => 9999,
					'primary' => []
				]
			],
			'response' => [
				'errors' => [
					'jobType' => [
						'identifier' => 'not-belong',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'primary' => [
						'identifier' => 'empty',
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
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/documents/formats',
				'auth' => 'customer',
				'body' => [
					'jobType' => 1,
					'primary' => ['xml', 'pdf'],
					'extra' => ['aci', 'zoo', 'zap']
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'jobType' => [
						'id' => 1,
						'isCommercial' => false,
						'isPayable' => true,
						'title' => new Dynamic(Dynamic::STRING),
						'local' => new Dynamic(function($v){
							return is_array($v) || $v === null;
						})
					],
					'primary' => ['xml', 'pdf'],
					'extra' => ['aci', 'zoo', 'zap']
				]
			]
		];
	},
	'tryCreatedAgain' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/settings/documents/formats',
				'auth' => 'customer',
				'body' => [
					'jobType' => 1,
					'primary' => ['xml']
				]
			],
			'response' => [
				'errors' => [
					'jobType' => [
						'identifier' => 'already-taken',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
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
					.'/settings/documents/formats',
				'auth' => 'customer',
				'body' => [
					'jobType' => 2,
					'primary' => ['pdf'],
					'extra' => []
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'jobType' => [
						'id' => 1,
						'isCommercial' => false,
						'isPayable' => true,
						'title' => new Dynamic(Dynamic::STRING),
						'local' => new Dynamic(function($v){
							return is_array($v) || $v === null;
						})
					],
					'primary' => ['xml', 'pdf'],
					'extra' => ['aci', 'zoo', 'zap']
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
					.'/settings/documents/formats/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'jobType' => 2,
					'primary' => ['pdf'],
					'extra' => []
				]
			]
		];
	},
	'updateTheSame' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id')
					.'/settings/documents/formats/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'jobType' => 2,
					'primary' => ['pdf'],
					'extra' => []
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
					.'/settings/documents/formats',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'jobType' => [
						'id' => 2,
						'isCommercial' => false,
						'isPayable' => true,
						'title' => new Dynamic(Dynamic::STRING),
						'local' => new Dynamic(function($v){
							return is_array($v) || $v === null;
						})
					],
					'primary' => ['pdf'],
					'extra' => []
				],
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['id'] == $capture->get('create.id');
				})
			]
		];
	},
	'updateFormats' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$session->get('user.id')
					.'/settings/documents/formats/'.$capture->get('create.id'),
				'auth' => 'customer',
				'body' => [
					'extra' => ['zoo', 'aci']
				]
			]
		];
	},
	'getWithUpdatedExtra' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id')
					.'/settings/documents/formats',
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'jobType' => [
						'id' => 2,
						'isCommercial' => false,
						'isPayable' => true,
						'title' => new Dynamic(Dynamic::STRING),
						'local' => new Dynamic(function($v){
							return is_array($v) || $v === null;
						})
					],
					'primary' => ['pdf'],
					'extra' => ['zoo', 'aci']
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
					.'/settings/documents/formats/'.$capture->get('create.id'),
				'auth' => 'customer'
			]
		];
	},
	'tryUpdateDeleted' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$session->get('user.id')
					.'/settings/documents/formats/'.$capture->get('create.id'),
				'auth' => 'customer'
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},
];