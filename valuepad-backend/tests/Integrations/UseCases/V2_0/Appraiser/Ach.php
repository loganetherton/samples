<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Core\Appraiser\Enums\AchAccountType;
use Ascope\QA\Integrations\Checkers\Dynamic;

return [
	'getEmpty' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/ach',
			],
			'response' => [
				'body' => [
					'bankName' => null,
					'accountNumber' => null,
					'accountType' => null,
					'routing' => null
				]
			]
		];
	},
	'validate' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$session->get('user.id').'/ach',
				'body' => [
					'bankName' => ' ',
					'accountNumber' => '123456789012345678901',
					'routing' => '123'
				]
			],
			'response' => [
				'errors' => [
					'bankName' => [
						'identifier' => 'empty',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'accountNumber' => [
						'identifier' => 'length',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'accountType' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'routing' => [
						'identifier' => 'length',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},
	'validate2' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$session->get('user.id').'/ach',
				'body' => [
					'accountNumber' => '123456789 1234567890',
					'routing' => '1234H6789'
				]
			],
			'response' => [
				'errors' => [
					'bankName' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'accountNumber' => [
						'identifier' => 'numeric',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'accountType' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
					'routing' => [
						'identifier' => 'numeric',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					],
				]
			]
		];
	},
	'replace' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$session->get('user.id').'/ach',
				'body' => [
					'bankName' => 'Bank of America',
					'accountNumber' => '12345678901234567890',
					'accountType' => AchAccountType::CHECKING,
					'routing' => '123456789'
				]
			],
			'response' => [
				'body' => [
					'bankName' => 'Bank of America',
					'accountNumber' => '7890',
					'accountType' => AchAccountType::CHECKING,
					'routing' => '6789'
				]
			],
            'push' => [
                'body' => [
                    [
                        'type' => 'appraiser',
                        'event' => 'update-ach',
                        'appraiser' => new Dynamic(Dynamic::INT)
                    ]
                ]
            ]
		];
	},
	'get' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/ach',
			],
			'response' => [
				'body' => [
					'bankName' => 'Bank of America',
					'accountNumber' => '7890',
					'accountType' => AchAccountType::CHECKING,
					'routing' => '6789'
				]
			]
		];
	},
	'replaceAgain' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'PUT /appraisers/'.$session->get('user.id').'/ach',
				'body' => [
					'bankName' => 'Chase',
					'accountNumber' => '11111111111111111111',
					'accountType' => AchAccountType::SAVING,
					'routing' => '111111111'
				]
			],
			'response' => [
				'body' => [
					'bankName' => 'Chase',
					'accountNumber' => '1111',
					'accountType' => AchAccountType::SAVING,
					'routing' => '1111'
				]
			]
		];
	},

	'getReplaced' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/ach',
			],
			'response' => [
				'body' => [
					'bankName' => 'Chase',
					'accountNumber' => '1111',
					'accountType' => AchAccountType::SAVING,
					'routing' => '1111'
				]
			]
		];
	},
];