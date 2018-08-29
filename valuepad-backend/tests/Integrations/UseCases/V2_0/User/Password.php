<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\User\Entities\Token;

$appraiser = uniqid('appraiser');
$newPassword = uniqid('password');

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
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'createAppraiser:init' => function(Runtime $runtime) use ($appraiser){

		$capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => $appraiser,
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
				'body' => $data
			]
		];
	},

	'tryRestPasswordWithWrongUsername' => [
		'request' => [
			'url' => 'POST /password/reset',
			'auth' => 'guest',
			'body' => [
				'username' => 'unknown',
			]
		],
		'response' => [
			'errors' => [
				'username' => [
					'identifier' => 'user-not-found',
					'message' => new Dynamic(Dynamic::STRING),
					'extra' => []
				]
			]
		]
	],

	'requestResetPassword' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /password/reset',
				'auth' => 'guest',
				'body' => [
					'username' => $capture->get('createAppraiser.username'),
				]
			],
			'emails' => [
				'body' => [
					[
						'from' => [
							'no-reply@valuepad.com' => 'The ValuePad Team'
						],
						'to' => [
							$capture->get('createAppraiser.email') => $capture->get('createAppraiser.firstName')
								.' '.$capture->get('createAppraiser.lastName')
						],
						'subject' => 'Request to Reset Password',
						'contents' => new Dynamic(Dynamic::STRING)
					]
				]
			]
		];
	},
	'provideToken:init' => function(Runtime $runtime){
		return [
			'raw' => function(EntityManagerInterface $em) use ($runtime){

				/**
				 * @var Token $token
				 */
				$token = $em->getRepository(Token::class)
					->findOneBy(['user' => $runtime->getCapture()->get('createAppraiser.id')]);

				$runtime->getCapture()->add('provideToken', ['token' => $token->getValue()]);
			}
		];
	},
	'tryChangePasswordBefore' => [
		'request' => [
			'url' => 'POST /password/change',
			'auth' => 'guest',
			'body' => [
				'token' => 'asddasd',
				'password' => $newPassword
			]
		],
		'response' => [
			'errors' => [
				'token' => [
					'identifier' => 'invalid',
					'message' => new Dynamic(Dynamic::STRING),
					'extra' => []
				]
			]
		]
	],
	'changePassword' => function(Runtime $runtime) use ($newPassword){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /password/change',
				'auth' => 'guest',
				'body' => [
					'token' => $capture->get('provideToken.token'),
					'password' => $newPassword
				]
			]
		];
	},
	'tryChangePasswordAfter' => function(Runtime $runtime) use ($newPassword){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /password/change',
				'auth' => 'guest',
				'body' => [
					'token' => $capture->get('provideToken.token'),
					'password' => $newPassword
				]
			],
			'response' => [
				'errors' => [
					'token' => [
						'identifier' => 'invalid',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'loginAppraiser' => function(Runtime $runtime) use ($newPassword){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /sessions',
				'auth' => 'guest',
				'body' => [
					'username' => $capture->get('createAppraiser.username'),
					'password' => $newPassword
				]
			]
		];
	}
];