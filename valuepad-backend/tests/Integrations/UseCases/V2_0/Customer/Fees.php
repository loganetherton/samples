<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Response;


return [
	'createCustomer:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => 'customerfeetest2',
				'password' => 'password',
				'name' => 'customerfeetest2'
			],
		]
	],
	'signinCustomer:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'customerfeetest2',
				'password' => 'password'
			]
		]
	],
	'addJobType1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test 1'
				]
			]
		];
	},
	'addJobType2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test 2'
				]
			]
		];
	},
	'invite:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/invitations',
				'body' => [
					'ascAppraiser' => 4
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				]
			]
		];
	},


	'create1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/customers/'
					.$capture->get('createCustomer.id').'/fees',
				'body' => [
					'jobType' => $capture->get('addJobType1.id'),
					'amount' => 2091.92
				]
			]
		];
	},

	'create2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');
		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/customers/'
					.$capture->get('createCustomer.id').'/fees',
				'body' => [
					'jobType' => $capture->get('addJobType2.id'),
					'amount' => 892.09
				]
			]
		];
	},

	'tryGetAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');


		return [
			'request' => [
				'url' => 'GET /customers/'
					.$capture->get('createCustomer.id').'/appraisers/'
					.$session->get('user.id').'/fees',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				]
			],
			'response' => [
				'status' => Response::HTTP_NOT_FOUND
			]
		];
	},

	'accept:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/invitations/'
					.$capture->get('invite.id').'/accept'
			]
		];
	},

	'getAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /customers/'
					.$capture->get('createCustomer.id').'/appraisers/'
					.$session->get('user.id').'/fees',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				]
			],
			'response' => [
				'body' => [
					[
						'id' => new Dynamic(Dynamic::INT),
						'jobType' => [
							'id' => new Dynamic(function($v) use ($capture){
								return in_array($v, [
									$capture->get('addJobType1.id'),
									$capture->get('addJobType2.id')
								]);
							}),
							'isCommercial' => false,
							'isPayable' => true,
							'title' => new Dynamic(Dynamic::STRING),
							'local' => null
						],
						'amount' => 2091.92
					],
					[
						'id' => new Dynamic(Dynamic::INT),
						'jobType' => [
							'id' => new Dynamic(function($v) use ($capture){
								return in_array($v, [
									$capture->get('addJobType1.id'),
									$capture->get('addJobType2.id')
								]);
							}),
							'isCommercial' => false,
							'isPayable' => true,
							'title' => new Dynamic(Dynamic::STRING),
							'local' => null
						],
						'amount' => 892.09
					],
				]
			]
		];
	},
];
