<?php
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Response;
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

return [
	'all' => function(Runtime $runtime){
		return [
			'request' => [
				'url' => 'GET /location/states/CA/counties',
				'includes' => ['state', 'zips']
			],
			'response' => [
				'body' => [
					'id' => $runtime->getHelper()->county('MONO', 'CA'),
					'title' => 'MONO',
					'state' => [
						'code' => 'CA',
						'name' => 'California'
					],
					'zips' => new Dynamic(function($data){
						return count($data) > 1 && is_vector($data);
					})
				],
				'filter' => new FirstFilter(function($key, $data) use ($runtime){
					return $data['id'] == $runtime->getHelper()->county('MONO', 'CA');
				}),
			]
		];
	},
	'one' => function(Runtime $runtime){
		return [
			'request' => [
				'url' => 'GET /location/states/CA/counties/'. $runtime->getHelper()->county('MONO', 'CA'),
				'includes' => ['state', 'zips']
			],
			'response' => [
				'body' => [
					'id' => $runtime->getHelper()->county('MONO', 'CA'),
					'title' => 'MONO',
					'state' => [
						'code' => 'CA',
						'name' => 'California'
					],
					'zips' => new Dynamic(function($data){
						return count($data) > 1 && is_vector($data);
					})
				]
			]
		];
	},
	'withFilter' => [
		'request' => [
			'url' => 'GET /location/states/CA/counties',
			'parameters' => [
				'filter' => [
					'counties' => '219,224,9910'
				]
			]
		],
		'response' => [
			'assert' => function(Response $response){
				$data = $response->getBody();
				return $data[0]['id'] != $data[1]['id']
				&& in_array($data[0]['id'], [219,224])
				&& in_array($data[1]['id'], [219,224]);
			},
			'total' => 2
		]
	],
];