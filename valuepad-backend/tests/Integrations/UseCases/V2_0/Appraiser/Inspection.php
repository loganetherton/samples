<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Tests\Integrations\Support\Filters\MessageAndExtraFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Log\Enums\Action;

$dueDate = (new DateTime('+5 days'))->format(DateTime::ATOM);
$estimatedCompletionDate = (new DateTime('+4 days'))->format(DateTime::ATOM);
$scheduledAt = (new DateTime('+3 days'))->format(DateTime::ATOM);
$completedAt = (new DateTime('-1 days'))->format(DateTime::ATOM);

return [
	'createOrder:init' => function(Runtime $runtime) use ($dueDate){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['dueDate'] = $dueDate;

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $data
			]
		];
	},
	'accept:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/accept',
			]
		];
	},

	'validateScheduleInspection1' => function(Runtime $runtime) use ($dueDate){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$scheduledAt = (new DateTime('-1 day'))->format(DateTime::ATOM);
		$estimatedCompletionDate = (new DateTime($dueDate))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/schedule-inspection',
				'body' => [
					'scheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			],
			'response' => [
				'errors' => [
					'estimatedCompletionDate' => [
						'identifier' => 'limit'
					]
				],
				'filter' => new MessageAndExtraFilter()
			]
		];
	},

	'validateScheduleInspection3' => function(Runtime $runtime) use ($estimatedCompletionDate){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$scheduledAt = new DateTime($estimatedCompletionDate);
		$scheduledAt->modify('+1 day');
		$scheduledAt = $scheduledAt->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/schedule-inspection',
				'body' => [
					'scheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			],
			'response' => [
				'errors' => [
					'scheduledAt' => [
						'identifier' => 'limit'
					]
				],
				'filter' => new MessageAndExtraFilter()
			]
		];
	},

	'scheduleInspection' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/schedule-inspection',
				'body' => [
					'scheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			],
			'push' => [
				'body' => [
					'type' => 'order',
					'event' => 'update-process-status',
					'order' => $capture->get('createOrder.id'),
					'scheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate,
					'oldProcessStatus' => ProcessStatus::ACCEPTED,
					'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED
				],
				'single' => true
			],
			'live' => [
				'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:update-process-status',
						'data' => [
							'order' => new Dynamic(function($data) use ($capture){
								return $data['id'] == $capture->get('createOrder.id');
							}),
							'oldProcessStatus' => ProcessStatus::ACCEPTED,
							'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
							'scheduledAt' => $scheduledAt,
							'estimatedCompletionDate' => $estimatedCompletionDate
						]
					]
				]
			],
			'emails' => [
				'body' => []
			],

			'mobile' => [
				'body' => []
			]
		];
	},

	'getScheduled' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id'),
				'includes' => [
					'inspectionScheduledAt',
					'inspectionCompletedAt',
					'estimatedCompletionDate',
					'processStatus'
				]
			],
			'response' => [
				'body' => [
					'inspectionScheduledAt' => $scheduledAt,
					'inspectionCompletedAt' => null,
					'estimatedCompletionDate' => $estimatedCompletionDate,
					'processStatus' => 'inspection-scheduled'
				],
				'filter' => new ItemFieldsFilter([
					'inspectionScheduledAt',
					'inspectionCompletedAt',
					'estimatedCompletionDate',
					'processStatus'
				], true)
			]
		];
	},

	'completeInspection' => function(Runtime $runtime) use ($estimatedCompletionDate, $completedAt){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/complete-inspection',
				'body' => [
					'completedAt' => $completedAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			],
			'push' => [
				'body' => [
					'type' => 'order',
					'event' => 'update-process-status',
					'order' => $capture->get('createOrder.id'),
					'completedAt' => $completedAt,
					'estimatedCompletionDate' => $estimatedCompletionDate,
					'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
					'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED
				],
				'single' => true
			],
			'live' => [
				'body' => [
                    [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
                        'event' => 'order:create-log',
                        'data' => new Dynamic(function($value){
                            return $value['action'] == Action::UPDATE_PROCESS_STATUS;
                        })
                    ],
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:update-process-status',
						'data' => [
							'order' => new Dynamic(function($data) use ($capture){
								return $data['id'] == $capture->get('createOrder.id');
							}),
							'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
							'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
							'completedAt' => $completedAt,
							'estimatedCompletionDate' => $estimatedCompletionDate
						]
					]
				]
			],
			'emails' => [
				'body' => []
			]
		];
	},

	'getCompleted' => function(Runtime $runtime) use ($completedAt, $scheduledAt, $estimatedCompletionDate){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id'),
				'includes' => [
					'inspectionScheduledAt',
					'inspectionCompletedAt',
					'estimatedCompletionDate',
					'processStatus'
				]
			],
			'response' => [
				'body' => [
					'inspectionScheduledAt' => $scheduledAt,
					'inspectionCompletedAt' => $completedAt,
					'estimatedCompletionDate' => $estimatedCompletionDate,
					'processStatus' => 'inspection-completed'
				],
				'filter' => new ItemFieldsFilter([
					'inspectionScheduledAt',
					'inspectionCompletedAt',
					'estimatedCompletionDate',
					'processStatus'
				], true)
			]
		];
	},

];
