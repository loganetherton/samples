<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Integrations\Checkers\Dynamic;

$config = [
	'breaker:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'addClientDesc:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/clients',
				'auth' => 'customer',
				'body' => [
					'name' => 'zzzzzzzzzzzzzzzzzzzzzzzzzz'
				]
			]
		];
	},

	'createOrderDesc:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClientDesc.id'),
			'clientDisplayedOnReport' => $capture->get('addClientDesc.id')
		]);

		$data['property']['contacts'] = [
			[
				'type' => 'borrower',
				'firstName' => 'zzzzz',
				'lastName' => 'zzzzz',
				'middleName' => 'Mike',
				'homePhone' => '(333) 444-4444',
				'cellPhone' => '(444) 555-5555',
				'workPhone' => '(555) 666-6666',
				'email' => 'george.smith@test.org'
			],
		];

		$data['property']['bestPersonToContact'] = 'borrower';
		$data['fileNumber'] = 'zzzzzzz';
		$data['orderedAt'] = (new DateTime('-1 minutes'))->format(DateTime::ATOM);
		$data['dueDate'] = (new DateTime('+21 years'))->format(DateTime::ATOM);
		$data['property']['address1'] = '99999999999 zzzzzzzzzzzzzzzzzzzzzzzzzz';
		$data['property']['address2'] = '99999999999 zzzzzzzzzzzzzzzzzzzzzzzzzz';

		$data['property']['city'] = 'zzzzzzzzzzzzzzzzzzzzzzzzzz';
		$data['property']['zip'] = '99999';
		$data['property']['state'] = 'WY';
		$data['property']['county'] = $runtime->getHelper()->county('SUBLETTE', 'WY');

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

	'addClientAsc:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/clients',
				'auth' => 'customer',
				'body' => [
					'name' => 'AAAAAAAAAAAAAAAAAAAAAAAAA'
				]
			]
		];
	},

	'createOrderAsc:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClientAsc.id'),
			'clientDisplayedOnReport' => $capture->get('addClientAsc.id')
		]);

		$data['property']['contacts'] = [
			[
				'type' => 'borrower',
				'firstName' => 'AAAAA',
				'lastName' => 'AAAAA',
				'middleName' => 'Mike',
				'homePhone' => '(333) 444-4444',
				'cellPhone' => '(444) 555-5555',
				'workPhone' => '(555) 666-6666',
				'email' => 'george.smith@test.org'
			],
		];

		$data['property']['bestPersonToContact'] = 'borrower';
		$data['fileNumber'] = 'AAAAAAA';
		$data['orderedAt'] = (new DateTime('2008-01-01 09:21:22'))->format(DateTime::ATOM);
		$data['dueDate'] = (new DateTime('+ 10 days'))->format(DateTime::ATOM);
		$data['property']['address1'] = '000000000 AAAAAAAAAAAAAAAAAAAAAAAAA';
		$data['property']['address2'] = '000000000 AAAAAAAAAAAAAAAAAAAAAAAAA';

		$data['property']['city'] = 'AAAAAAAAAAAAAAAAAAAAAAAAA';
		$data['property']['zip'] = '11111';
		$data['property']['state'] = 'AK';
		$data['property']['county'] = $runtime->getHelper()->county('JUNEAU', 'AK');


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
];

$cases = [
	'clientName',
	'borrowerName',
	'fileNumber',
	'orderedAt',
	'property.address',
	'property.state.code',
	'property.state.name',
	'property.city',
	'property.zip'
];

foreach ($cases as $case){

	if ($case !== 'borrowerName'){
		$config['sortBy'.$case.'Asc'] = function(Runtime $runtime) use ($case){
			$session = $runtime->getSession('appraiser');
			$capture = $runtime->getCapture();

			return [
				'request' => [
					'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
					'parameters' => [
						'orderBy' => $case.':asc'
					]
				],
				'response' => [
					'body' => [
						'id' => $capture->get('createOrderAsc.id'),
					],
					'filter' => new CompositeFilter([
						new FirstFilter(function($k, $v) use ($capture){
							return true;
						}),
						new ItemFieldsFilter(['id'], true)
					])
				]
			];
		};
	}

	$config['sortBy'.$case.'Desc'] = function(Runtime $runtime) use ($case){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'orderBy' => $case.':desc'
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderDesc.id'),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	};
}

$config = array_merge($config, [
	'sortByDueDateAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'orderBy' => 'dueDate:asc'
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(function($id) use ($capture){
						return $id != $capture->get('createOrderDesc.id');
					}),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},
	'sortByDueDateDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'orderBy' => 'dueDate:desc'
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderDesc.id'),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},

	'accept' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderAsc.id').'/accept',
			]
		];
	},
	'sortByProcessStatusAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['processStatus'],
				'parameters' => [
					'orderBy' => 'processStatus:asc'
				]
			],
			'response' => [
				'body' => [
					'processStatus' => 'accepted',
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['processStatus'], true)
				])
			]
		];
	},
	'sortByProcessStatusDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['processStatus'],
				'parameters' => [
					'orderBy' => 'processStatus:desc'
				]
			],
			'response' => [
				'body' => [
					'processStatus' => new Dynamic(function($status){
						return $status !== 'accepted';
					}),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['processStatus'], true)
				])
			]
		];
	},
	'sortByAcceptedAtAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['acceptedAt'],
				'parameters' => [
					'orderBy' => 'acceptedAt:asc'
				]
			],
			'response' => [
				'body' => [
					'acceptedAt' => null,
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['acceptedAt'], true)
				])
			]
		];
	},
	'sortByAcceptedAtDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['acceptedAt'],
				'parameters' => [
					'orderBy' => 'acceptedAt:desc'
				]
			],
			'response' => [
				'body' => [
					'acceptedAt' => new Dynamic(Dynamic::DATETIME),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['acceptedAt'], true)
				])
			]
		];
	},

	'scheduleInspection:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderAsc.id').'/schedule-inspection',
				'body' => [
					'scheduledAt' => (new DateTime('+2 days'))->format(DateTime::ATOM),
					'estimatedCompletionDate' => (new DateTime('+ 3 days'))->format(DateTime::ATOM),
				]
			]
		];
	},

	'sortByInspectionScheduledAtAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['inspectionScheduledAt'],
				'parameters' => [
					'orderBy' => 'inspectionScheduledAt:asc'
				]
			],
			'response' => [
				'body' => [
					'inspectionScheduledAt' => null,
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['inspectionScheduledAt'], true)
				])
			]
		];
	},
	'sortByInspectionScheduledAtDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['inspectionScheduledAt'],
				'parameters' => [
					'orderBy' => 'inspectionScheduledAt:desc'
				]
			],
			'response' => [
				'body' => [
					'inspectionScheduledAt' => new Dynamic(Dynamic::DATETIME),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['inspectionScheduledAt'], true)
				])
			]
		];
	},

	'completeInspection:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderAsc.id').'/complete-inspection',
				'body' => [
					'completedAt' => (new DateTime('-1 days'))->format(DateTime::ATOM),
					'estimatedCompletionDate' => (new DateTime('+ 4 days'))->format(DateTime::ATOM),
				]
			]
		];
	},

	'sortByInspectionCompletedAtAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['inspectionCompletedAt'],
				'parameters' => [
					'orderBy' => 'inspectionCompletedAt:asc'
				]
			],
			'response' => [
				'body' => [
					'inspectionCompletedAt' => null,
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['inspectionCompletedAt'], true)
				])
			]
		];
	},
	'sortByInspectionCompletedAtDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['inspectionCompletedAt'],
				'parameters' => [
					'orderBy' => 'inspectionCompletedAt:desc'
				]
			],
			'response' => [
				'body' => [
					'inspectionCompletedAt' => new Dynamic(Dynamic::DATETIME),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['inspectionCompletedAt'], true)
				])
			]
		];
	},

	'sortByEstimatedCompletionDateAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['estimatedCompletionDate'],
				'parameters' => [
					'orderBy' => 'estimatedCompletionDate:asc'
				]
			],
			'response' => [
				'body' => [
					'estimatedCompletionDate' => null,
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['estimatedCompletionDate'], true)
				])
			]
		];
	},
	'sortByEstimatedCompletionDateDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['estimatedCompletionDate'],
				'parameters' => [
					'orderBy' => 'estimatedCompletionDate:desc'
				]
			],
			'response' => [
				'body' => [
					'estimatedCompletionDate' => new Dynamic(Dynamic::DATETIME),
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['estimatedCompletionDate'], true)
				])
			]
		];
	},
	'createOrderForPuttingOnHold:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'putOrderOnHold:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForPuttingOnHold.id').'/workflow/on-hold',
				'auth' => 'customer',
				'body' => [
					'explanation' => 'I need to think.'
				]
			]
		];
	},

	'sortByPutOnHoldAtAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['putOnHoldAt'],
				'parameters' => [
					'orderBy' => 'putOnHoldAt:asc'
				]
			],
			'response' => [
				'body' => [
					'putOnHoldAt' => null
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['putOnHoldAt'], true)
				])
			]
		];
	},
	'sortByPutOnHoldAtDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['putOnHoldAt'],
				'parameters' => [
					'orderBy' => 'putOnHoldAt:desc'
				]
			],
			'response' => [
				'body' => [
					'putOnHoldAt' => new Dynamic(Dynamic::DATETIME)
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['putOnHoldAt'], true)
				])
			]
		];
	},

	'createOrderForComplete:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'completeOrder:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForComplete.id').'/workflow/completed',
				'auth' => 'customer'
			]
		];
	},

	'sortByCompletedAtAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['completedAt'],
				'parameters' => [
					'orderBy' => 'completedAt:asc'
				]
			],
			'response' => [
				'body' => [
					'completedAt' => null
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['completedAt'], true)
				])
			]
		];
	},
	'sortByCompletedAtDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['completedAt'],
				'parameters' => [
					'orderBy' => 'completedAt:desc'
				]
			],
			'response' => [
				'body' => [
					'completedAt' => new Dynamic(Dynamic::DATETIME)
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['completedAt'], true)
				])
			]
		];
	},

	'createOrderForRequestRevision:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'requestRevision:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForComplete.id').'/workflow/revision-pending',
				'auth' => 'customer'
			]
		];
	},


	'sortByRevisionReceivedAtAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['revisionReceivedAt'],
				'parameters' => [
					'orderBy' => 'revisionReceivedAt:asc'
				]
			],
			'response' => [
				'body' => [
					'revisionReceivedAt' => null
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['revisionReceivedAt'], true)
				])
			]
		];
	},

	'sortByRevisionReceivedAtDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['revisionReceivedAt'],
				'parameters' => [
					'orderBy' => 'revisionReceivedAt:desc'
				]
			],
			'response' => [
				'body' => [
					'revisionReceivedAt' => new Dynamic(Dynamic::DATETIME)
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return true;
					}),
					new ItemFieldsFilter(['revisionReceivedAt'], true)
				])
			]
		];
	},
]);

return $config;