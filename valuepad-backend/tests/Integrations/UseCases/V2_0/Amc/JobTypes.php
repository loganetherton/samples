<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Integrations\Checkers\Dynamic;

$customer = uniqid('customer');

return [
	'getAll' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$amcSession = $runtime->getSession('amc');

		return [
			'request' => [
				'url' => 'GET /amcs/'.$amcSession->get('user.id')
					.'/customers/'.$customerSession->get('user.id').'/job-types',
                'auth' => 'amc'
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'isCommercial' => false,
					'isPayable' => true,
					'title' => new Dynamic(Dynamic::STRING),
					'local' => new Dynamic(function($v){
						return is_array($v) && count($v) > 0;
					})
				],
				'total' => ['>', 10],
				'filter' => new FirstFilter(function(){
					return true;
				})
			]
		];
	}
];