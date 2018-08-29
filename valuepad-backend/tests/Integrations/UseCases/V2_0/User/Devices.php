<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Core\User\Enums\Platform;
use Ascope\QA\Integrations\Checkers\Dynamic;

return [
	'create' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /users/'.$session->get('user.id').'/devices',
				'body' => [
					'token' => '5e82ea30694e83a4fd6bd8f68ca1cfe8bc113bb184fc9fcf8eb202907911e645',
					'platform' => Platform::IOS
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'token' => '5e82ea30694e83a4fd6bd8f68ca1cfe8bc113bb184fc9fcf8eb202907911e645',
					'platform' => Platform::IOS
				]
			]
		];
	},
	'replace' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /users/'.$session->get('user.id').'/devices',
				'body' => [
					'token' => '5e82ea30694e83a4fd6bd8f68ca1cfe8bc113bb184fc9fcf8eb202907911e645',
					'platform' => Platform::IOS
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('create.id'),
					'token' => '5e82ea30694e83a4fd6bd8f68ca1cfe8bc113bb184fc9fcf8eb202907911e645',
					'platform' => Platform::IOS
				]
			]
		];
	},

	'delete' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /users/'.$session->get('user.id').'/devices/'.$capture->get('create.id')
			]
		];
	}
];