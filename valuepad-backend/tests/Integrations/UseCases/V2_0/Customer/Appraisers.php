<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;

return [
	'createCustomer:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => 'customerappraisertest1',
				'password' => 'password',
				'name' => 'customerappraisertest1'
			]
		]
	],
	'signinCustomer:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'customerappraisertest1',
				'password' => 'password'
			]
		]
	],
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
	'tryGetAppraisers' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/appraisers',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				]
			],
			'response' => [
				'total' => 0
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
					.$capture->get('invite.id').'/accept',
			]
		];
	},

	'getAppraisers' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/appraisers',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				]
			],
			'response' => [
				'body' => [
					'id' => $session->get('user.id'),
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($session){ return $v['id'] == $session->get('user.id'); }),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	}
];