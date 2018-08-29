<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Response;

return [
	'validate' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/fees',
				'body' => [
					'jobType' => 1000,
					'amount' => -10.99
				]
			],
			'response' => [
				'errors' => [
					'jobType' => [
						'identifier' => 'exists',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'amount' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'create1' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/fees',
				'body' => [
					'jobType' => 10,
					'amount' => 10.99
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'jobType' => [
						'id' => 10,
						'title' => new Dynamic(Dynamic::STRING)
					],
					'amount' => 10.99
				]
			]
		];
	},
	'tryCreate2' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/fees',
				'body' => [
					'jobType' => 10,
					'amount' => 10.99
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
	'create2' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/fees',
				'body' => [
					'jobType' => 13,
					'amount' => 0.99
				]
			]
		];
	},

	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/fees',
			],
			'response' => [
				'total' => ['>=', 2]
			]
		];
	},
	'tryUpdate' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/fees/'.$capture->get('create2.id'),
				'body' => [
					'jobType' => 14,
					'amount' => 0.99
				]
			],
			'response' => [
				'errors' => [
					'jobType' => [
						'identifier' => 'read-only',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'update' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'PATCH /appraisers/'.$session->get('user.id').'/fees/'.$capture->get('create2.id'),
				'body' => [
					'amount' => 40.45
				]
			]
		];
	},
	'getAllAfterUpdating' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/fees',
			],
			'response' => [
				'body' => [
					'amount' => 40.45
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create2.id');
					}),
					new ItemFieldsFilter(['amount'], true)
				])
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'DELETE /appraisers/'.$session->get('user.id').'/fees/'.$capture->get('create1.id'),
			]
		];
	},
	'getAllAfterDeleting' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/fees',
			],
			'response' => [
				'total' => ['>=', 1],
				'assert' => function(Response $response) use ($capture){
					$data = $response->getBody();

					if (!$data){
						return false;
					}

					foreach ($data as $row){
						if ($row['id'] == $capture->get('create2.id')){
							return true;
						}
					}

					return false;
				}
			]
		];
	},

];