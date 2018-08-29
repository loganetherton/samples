<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Customer\Enums\CompanyType;
use ValuePad\Tests\Integrations\Support\Filters\MessageAndExtraFilter;
use ValuePad\Core\User\Validation\Rules\Password;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

return [
	'validate' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => '#$%#$%',
				'password' => 'üîøåéaaaaaaa',
				'name' => ' ',
				'phone' => '(312) 44224422'
			]
		],
		'response' => [
			'errors' => [
				'username' => [
					'identifier' => 'format'
				],
				'password' => [
					'identifier' => 'format'
				],
				'name' => [
					'identifier' => 'empty'
				],
				'phone' => [
					'identifier' => 'format'
				]
			],
			'filter' => new MessageAndExtraFilter()
		]
	],
	'create' => [
		'request' => [
			'url' => 'POST /customers',
			'includes' => ['phone'],
			'body' => [
				'username' => 'customertest',
				'phone' => '(555) 777-9999',
				'password' => Password::ALLOWED_CHARACTERS.' aAzZ09',
				'name' => 'google',
				'companyType' => CompanyType::CREDIT_UNION,
			]
		],
		'response' => [
			'body' => [
				'id' => new Dynamic(Dynamic::INT),
				'username' => 'customertest',
				'name' => 'google',
				'phone' => '(555) 777-9999',
				'displayName' => 'google',
				'companyType' => CompanyType::CREDIT_UNION,
                'type' => 'customer'
			]
		]
	],

	'login:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'customertest',
				'password' => Password::ALLOWED_CHARACTERS.' aAzZ09'
			]
		]
	],

	'get' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'includes' => ['phone'],
				'headers' => [
					'Token' => $capture->get('login.token')
				]
			],
			'response' => [
				'body' => $capture->get('create')
			]
		];
	},
	'update' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /customers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('login.token')
				],
				'body' => [
					'phone' => '(555) 777-1111',
					'name' => 'Microsoft',
					'companyType' => CompanyType::MORTGAGE_BROKER,
				]
			]
		];
	},

	'getAfterUpdate' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$data = $capture->get('create');
		$data['phone'] = '(555) 777-1111';
		$data['name'] = 'Microsoft';
		$data['companyType'] = CompanyType::MORTGAGE_BROKER;
		$data['displayName'] = $data['name'];

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('create.id'),
				'auth' => 'guest',
				'includes' => ['phone'],
				'headers' => [
					'Token' => $capture->get('login.token')
				],
			],
			'response' => [
				'body' => $data
			]
		];
	},
];