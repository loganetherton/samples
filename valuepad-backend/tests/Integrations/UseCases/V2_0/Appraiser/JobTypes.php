<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Integrations\Checkers\Dynamic;

return [
	'getAll' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id')
					.'/customers/'.$customerSession->get('user.id').'/job-types'
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