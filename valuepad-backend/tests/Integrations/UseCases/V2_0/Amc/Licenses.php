<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\User\Enums\Status;

$amc2 = uniqid('amc');

$commons = [
	'update.expiresAt' => (new DateTime('+2 year'))->format('c')
];

return [
	'document:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],
	'validate' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/licenses',
				'auth' => 'amc',
				'body' => [
					'number' => ' ',
					'state' => 'CA',
					'expiresAt' => (new DateTime('-1 year'))->format('c'),
					'document' => [
						'token' => 'wrong token',
						'id' => $capture->get('document.id')
					],
					'coverage' => [
						[
							'county' => $runtime->getHelper()->county('SACRAMENTO', 'CA'), // belongs to the current state
							'zips' => ['94945'],
						],
						[
							'county' => $runtime->getHelper()->county('KOOCHICHING', 'MN')
						]
					]
				]
			],
			'response' => [
				'errors' => [
					'expiresAt' => [
						'identifier' => 'greater',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'document' => [
						'identifier' => 'permissions',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'coverage' => [
						'identifier' => 'collection',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => [
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'zips' => [
										'identifier' => 'exists',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							],
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'county' => [
										'identifier' => 'exists',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							]
						]
					]
				]
			]
		];
	},

	'create' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		$expiresAt = (new DateTime('+1 year'))->format('c');

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/licenses',
				'auth' => 'amc',
				'body' => [
					'number' => 'R000000041',
					'state' => 'MN',
					'expiresAt' => $expiresAt,
					'document' => [
						'token' => $capture->get('document.token'),
						'id' => $capture->get('document.id')
					],
					'coverage' => [
						[
							'county' => $runtime->getHelper()->county('SHERBURNE', 'MN'),
							'zips' => ['55377'],
						],
						[
							'county' => $runtime->getHelper()->county('KOOCHICHING', 'MN')
						]
					],
					'alias' => [
						'companyName' => 'DEF Company',
						'address1' => '5678 X Avenue',
						'address2' => 'Y Building',
						'city' => 'Los Angeles',
						'state' => 'CA',
						'zip' => '90210',
						'phone' => '(888) 807-2111',
						'fax' => '(888) 807-2112',
						'email' => 'def@company.biz',
					]
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'number' => 'R000000041',
					'state' => [
						'code' => 'MN',
						'name' => 'Minnesota'
					],
					'expiresAt' => $expiresAt,
					'document' => $capture->get('document'),
					'coverage' => [
						[
							'county' => [
								'id' => $runtime->getHelper()->county('SHERBURNE', 'MN'),
								'title' => 'SHERBURNE'
							],
							'zips' => ['55377'],
						],
						[
							'county' => [
								'id' => $runtime->getHelper()->county('KOOCHICHING', 'MN'),
								'title' => 'KOOCHICHING'
							],
							'zips' => []
						]
					],
					'alias' => [
						'companyName' => 'DEF Company',
						'address1' => '5678 X Avenue',
						'address2' => 'Y Building',
						'city' => 'Los Angeles',
						'state' => [
							'code' => 'CA',
							'name' => 'California'
						],
						'zip' => '90210',
						'phone' => '(888) 807-2111',
						'fax' => '(888) 807-2112',
						'email' => 'def@company.biz',
					]
				]
			]
		];
	},
	'tryCreateWithSameState' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');
		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/licenses',
				'auth' => 'amc',
				'body' => [
					'number' => 'R000000042',
					'state' => 'MN',
				]
			],
			'response' => [
				'errors' => [
					'state' => [
						'identifier' => 'unique'
					]
				],
				'filter' => new ItemFieldsFilter(['state.identifier'], true)
			]
		];
	},

	'createAmc2:init' => [
		'request' => [
			'url' => 'POST /amcs',
			'auth' => 'guest',
			'body' => [
				'username' => $amc2,
				'password' => 'password',
				'email' => 'bestamc@ever.org',
				'companyName' => 'Best AMC Ever!',
				'address1' => '123 Wall Str.',
				'address2' => '124B Wall Str.',
				'city' => 'New York',
				'zip' => '44211',
				'state' => 'NY',
				'lenders' => 'VMX, TTT, abc',
				'phone' => '(423) 553-1211',
				'fax' => '(423) 553-1212'
			]
		],
	],

	'approveAmc2:init' => function(Runtime $runtime){
		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$runtime->getCapture()->get('createAmc2.id'),
				'auth' => 'admin',
				'body' => [
					'status' => Status::APPROVED
				]
			]
		];
	},

	'signinAmc2:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $amc2,
				'password' => 'password'
			]
		]
	],

	'tryCreateWithTaken' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$expiresAt = (new DateTime('+1 year'))->format('c');

		return [
			'request' => [
				'url' => 'POST /amcs/'.$capture->get('createAmc2.id').'/licenses',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAmc2.token')
				],
				'body' => [
					'number' => 'R000000041',
					'state' => 'MN',
					'expiresAt' => $expiresAt
				]
			],
			'response' => [
				'errors' => [
					'number' => [
						'identifier' => 'already-taken',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'createWithSameNumber' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$expiresAt = (new DateTime('+1 year'))->format('c');

		return [
			'request' => [
				'url' => 'POST /amcs/'.$capture->get('createAmc2.id').'/licenses',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAmc2.token')
				],
				'body' => [
					'number' => 'R000000041',
					'state' => 'TX',
					'expiresAt' => $expiresAt
				]
			]
		];
	},

	'tryUpdateStateToExistent' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$capture->get('createAmc2.id').'/licenses/'.$capture->get('createWithSameNumber.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAmc2.token')
				],
				'body' => [
					'state' => 'MN',
				]
			],

			'response' => [
				'errors' => [
					'number' => [
						'identifier' => 'already-taken',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'tryChangeState' => function(Runtime $runtime) use ($commons){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
				'body' => [
					'state' => 'CA'
				]
			],
			'response' => [
				'errors' => [
					'coverage' => [
						'identifier' => 'collection',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => [
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'county' => [
										'identifier' => 'exists',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							],
							[
								'identifier' => 'dataset',
								'message' => new Dynamic(Dynamic::STRING),
								'extra' => [
									'county' => [
										'identifier' => 'exists',
										'message' => new Dynamic(Dynamic::STRING),
										'extra' => []
									]
								]
							]
						]
					]
				]
			]
		];
	},

	'updateAlias' => function (Runtime $runtime) {
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
				'body' => [
					'alias' => [
						'companyName' => 'XYZ Company',
						'address1' => '1234 Addr',
						'address2' => 'BBB',
						'city' => 'Abilene',
						'state' => 'TX',
						'zip' => '79601',
						'phone' => '(325) 437-3090',
						'fax' => '(325) 437-3091',
						'email' => 'kek@top.biz',
					]
				]
			]
		];
	},

	'getAfterAliasUpdate' => function (Runtime $runtime) {
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'number' => 'R000000041',
					'state' => [
						'code' => 'MN',
						'name' => 'Minnesota'
					],
					'expiresAt' => $capture->get('create.expiresAt'),
					'document' => $capture->get('document'),
					'coverage' => [
						[
							'county' => [
								'id' => $runtime->getHelper()->county('SHERBURNE', 'MN'),
								'title' => 'SHERBURNE'
							],
							'zips' => ['55377'],
						],
						[
							'county' => [
								'id' => $runtime->getHelper()->county('KOOCHICHING', 'MN'),
								'title' => 'KOOCHICHING'
							],
							'zips' => []
						]
					],
					'alias' => [
						'companyName' => 'XYZ Company',
						'address1' => '1234 Addr',
						'address2' => 'BBB',
						'city' => 'Abilene',
						'state' => [
							'code' => 'TX',
							'name' => 'Texas'
						],
						'zip' => '79601',
						'phone' => '(325) 437-3090',
						'fax' => '(325) 437-3091',
						'email' => 'kek@top.biz',
					]
				]
			]
		];
	},

	'update' => function(Runtime $runtime) use ($commons){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
				'body' => [
					'expiresAt' => $commons['update.expiresAt'],
					'document' => null,
					'coverage' => [],
					'alias' => null
				]
			]
		];
	},

	'createAliasOnExistingLicense' => function(Runtime $runtime) {
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
				'body' => [
					'alias' => [
						'companyName' => 'XYZ Company',
						'address1' => '1234 Addr',
						'address2' => 'BBB',
						'city' => 'Abilene',
						'state' => 'TX',
						'zip' => '79601',
						'phone' => '(325) 437-3090',
						'fax' => '(325) 437-3091',
						'email' => 'kek@top.biz',
					]
				]
			]
		];
	},


	'updateNumberAndState' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
				'body' => [
					'number' => 'R000000041',
					'state' => 'MN'
				]
			]
		];
	},

	'deleteAlias' => function(Runtime $runtime) {
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'PATCH /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
				'body' => [
					'alias' => null
				]
			]
		];
	},

	'get' => function(Runtime $runtime) use ($commons){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'number' => 'R000000041',
					'state' => [
						'code' => 'MN',
						'name' => 'Minnesota'
					],
					'expiresAt' => $commons['update.expiresAt'],
					'document' => null,
					'coverage' => [],
					'alias' => null
				]
			]
		];

	},
	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/licenses',
				'auth' => 'amc',
			],
			'response' => [
				'total' => 1
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /amcs/'.$session->get('user.id').'/licenses/'.$capture->get('create.id'),
				'auth' => 'amc',
			]
		];
	},

	'getAllAfterDelete' => function(Runtime $runtime){
		$session = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'GET /amcs/'.$session->get('user.id').'/licenses',
				'auth' => 'amc',
			],
			'response' => [
				'total' => 0
			]
		];
	},

	'createWithNoNumber' => function (Runtime $runtime) {
		$session = $runtime->getSession('amc');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /amcs/'.$session->get('user.id').'/licenses',
				'auth' => 'amc',
				'body' => [
					'number' => '',
					'state' => 'CA',
					'expiresAt' => (new DateTime('+1 year'))->format('c'),
					'document' => [
						'token' => $capture->get('document.token'),
						'id' => $capture->get('document.id')
					],
					'coverage' => [
						[
							'county' => $runtime->getHelper()->county('LOS ANGELES', 'CA')
						]
					]
				]
			],
			'response' => [
				'body' => [
					'number' => ''
				],
				'filter' => new ItemFieldsFilter(['number'], true)
			]
		];
	},

	'createWithNoNumber1' => function (Runtime $runtime) {
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /amcs/'.$capture->get('createAmc2.id').'/licenses',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAmc2.token')
				],
				'body' => [
					'number' => '',
					'state' => 'CA',
					'expiresAt' => (new DateTime('+1 year'))->format('c')
				]
			],
			'response' => [
				'body' => [
					'number' => ''
				],
				'filter' => new ItemFieldsFilter(['number'], true)
			]
		];
	}
];
