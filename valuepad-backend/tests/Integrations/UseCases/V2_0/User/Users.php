<?php
use Ascope\QA\Support\Response;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

return [
	'userExists' => function(Runtime $runtime){

		$username = $runtime->getConfig()
			->get('qa.integrations.sessions.credentials.appraiser.username');

		return [
			'request' => [
				'url' => 'GET /users/'.$username,
			],
			'response' => [
				'status' => Response::HTTP_OK
			]
		];
	},

	'userDoesNotExist' => [
	'request' => [
		'url' => 'GET /users/wrong.man@test.org',
	],
	'response' => [
		'status' => Response::HTTP_NOT_FOUND
	]
]
];