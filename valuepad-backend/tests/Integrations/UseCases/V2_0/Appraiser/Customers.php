<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;

return [
	'createCustomer1:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => 'appraisercustomertest1',
				'password' => 'password',
				'name' => 'a0order'
			]
		]
	],
	'signinCustomer1:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'appraisercustomertest1',
				'password' => 'password'
			]
		]
	],
	'invite1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer1.id').'/invitations',
				'body' => [
					'ascAppraiser' => 4
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer1.token')
				]
			]
		];

	},
	'accept1:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/invitations/'
					.$capture->get('invite1.id').'/accept',
			]
		];
	},

	'createCustomer2:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => 'appraisercustomertest2',
				'password' => 'password',
				'name' => 'z9order'
			]
		]
	],
	'signinCustomer2:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'appraisercustomertest2',
				'password' => 'password'
			]
		]
	],
	'invite2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer2.id').'/invitations',
				'body' => [
					'ascAppraiser' => 4
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer2.token')
				]
			]
		];

	},
	'accept2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/invitations/'
					.$capture->get('invite2.id').'/accept',
			]
		];
	},

	'getCustomersDesc' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/customers',
				'parameters' => [
					'orderBy' => 'name:desc'
				]
			],
			'response' => [
				'total' => ['>=', 2],
				'body' =>  [
					'name' => 'z9order'
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture) {
						return true;
					}),
					new ItemFieldsFilter(['name'], true)
				])
			]
		];
	},

	'getCustomersAsc' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/customers',
				'parameters' => [
					'orderBy' => 'name:asc'
				]
			],
			'response' => [
				'total' => ['>=', 2],
				'body' =>  [
					'name' => 'a0order'
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture) {
						return true;
					}),
					new ItemFieldsFilter(['name'], true)
				])
			]
		];
	},
];
