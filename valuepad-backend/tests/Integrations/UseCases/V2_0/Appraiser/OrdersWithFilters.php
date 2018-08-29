<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Response;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;

$data = [];

return [
	'addClient:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/clients',
				'auth' => 'customer',
				'body' => [
					'name' => 'super cool client'
				]
			]
		];
	},

	'createOrderWithClientName:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClient.id'),
			'clientDisplayedOnReport' => $capture->get('addClient.id')
		]);

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

	'filterClientName' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['clientName'],
				'parameters' => [
					'search' => [
						'clientName' => 'Super Cool'
					]
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderWithClientName.id'),
					'clientName' => 'super cool client'
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderWithClientName.id');
					}),
					new ItemFieldsFilter(['clientName', 'id'], true)
				])
			]
		];
	},

	'createOrderWithBorrowerName:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['property']['contacts'] = [
			[
				'type' => 'borrower',
				'firstName' => 'Super',
				'lastName' => 'Borrower',
				'middleName' => 'Mike',
				'homePhone' => '(333) 444-4444',
				'cellPhone' => '(444) 555-5555',
				'workPhone' => '(555) 666-6666',
				'email' => 'george.smith@test.org'
			],
		];

		$data['property']['bestPersonToContact'] = 'borrower';

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

	'filterBorrowerName' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['property'],
				'parameters' => [
					'search' => [
						'borrowerName' => 'super Mike Borrow'
					]
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderWithBorrowerName.id'),
					'property' => [
						'contacts' => [
							[
								'firstName' => 'Super',
								'lastName' => 'Borrower'
							]
						]
					]
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderWithBorrowerName.id');
					}),
					new ItemFieldsFilter([
						'property.contacts.0.firstName',
						'property.contacts.0.lastName',
						'id'
					], true)
				])
			]
		];
	},

	'createOrderForAccept:init' => function(Runtime $runtime){
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

	'accept1:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForAccept.id').'/accept',
			]
		];
	},

	'filterProcessStatusAccepted' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['processStatus'],
				'parameters' => [
					'filter' => [
						'processStatus' => 'accepted'
					]
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderForAccept.id'),
					'processStatus' => 'accepted'
				],
				'total' => ['>=', 1],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderForAccept.id');
					}),
					new ItemFieldsFilter(['processStatus', 'id'], true)
				])
			]
		];
	},

	'filterAcceptedAt' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['acceptedAt'],
				'parameters' => [
					'filter' => [
						'acceptedAt' => (new DateTime())->format('Y-m-d')
					],
					'perPage' => 100
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderForAccept.id'),
					'acceptedAt' => new Dynamic(function($value){
						return starts_with($value, (new DateTime())->format('Y-m-d'));
					})
				],
				'total' => ['>=', 1],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderForAccept.id');
					}),
					new ItemFieldsFilter(['acceptedAt', 'id'], true)
				])
			]
		];
	},

	'createOrderFileNumber1:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['fileNumber'] = 'TESTFILENUMBER123';

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

	'createOrderFileNumber2:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['fileNumber'] = 'TESTFILENUMBER124';

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

	'filterFileNumber' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'fileNumber' => 'TESTFILENUMBER123'
					]
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderFileNumber1.id'),
					'fileNumber' => 'TESTFILENUMBER123'
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderFileNumber1.id');
					}),
					new ItemFieldsFilter(['fileNumber', 'id'], true)
				])
			]
		];
	},

	'searchFileNumber' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'search' => [
						'fileNumber' => 'TESTFILENUMBER12'
					]
				]
			],
			'response' => [
				'assert' => function(Response $response){
					$data = $response->getBody();

					if (!$data){
						return false;
					}

					foreach ($data as $row){
						if (!starts_with($row['fileNumber'], 'TESTFILENUMBER12')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'searchFileNumberWithQuery' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'query' => 'TESTFILENUMBER12'
				]
			],
			'response' => [
				'assert' => function(Response $response){
					$data = $response->getBody();

					if (!$data){
						return false;
					}

					foreach ($data as $row){
						if (!starts_with($row['fileNumber'], 'TESTFILENUMBER12')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'createOrderOrderedAt:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['orderedAt'] = (new DateTime('2011-09-13 09:22:01'))->format(DateTime::ATOM);

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

	'createOrderOrderedAt2:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$data['orderedAt'] = (new DateTime('2011-10-13 09:22:01'))->format(DateTime::ATOM);

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

	'filterOrderedAt' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$orderedAt = (new DateTime('2011-09-13 09:22:01'))->format(DateTime::ATOM);
		$filterOrderedAt = (new DateTime('2011-09-13'))->format('Y-m-d');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'orderedAt' => $filterOrderedAt
					]
				],
				'includes' => ['orderedAt']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderOrderedAt.id'),
					'orderedAt' => $orderedAt
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderOrderedAt.id');
					}),
					new ItemFieldsFilter(['orderedAt', 'id'], true)
				])
			]
		];
	},

	'filterOrderedAtDay' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$orderedAt = (new DateTime('2011-09-13 09:22:01'))->format(DateTime::ATOM);
		$filterOrderedAt = (new DateTime('2011-09-13'))->format('Y-m-d');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'orderedAt' => [
							'day' => $filterOrderedAt
						]
					]
				],
				'includes' => ['orderedAt']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderOrderedAt.id'),
					'orderedAt' => $orderedAt
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderOrderedAt.id');
					}),
					new ItemFieldsFilter(['orderedAt', 'id'], true)
				])
			]
		];
	},

	'filterOrderedAtFrom' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'orderedAt' => [
							'from' => (new DateTime('2011-10-13'))->format('Y-m-d')
						]
					]
				],
				'includes' => ['orderedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['orderedAt'] === null
							|| new DateTime($item['orderedAt']) < new DateTime('2011-10-13')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterOrderedAtTo' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'orderedAt' => [
							'to' => (new DateTime('2011-10-12'))->format('Y-m-d')
						]
					]
				],
				'includes' => ['orderedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['orderedAt'] === null
							|| new DateTime($item['orderedAt']) > new DateTime('2011-10-12')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterOrderedAtMonth' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'orderedAt' => [
							'month' => 10
						]
					]
				],
				'includes' => ['orderedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ((int)(new DateTime($item['orderedAt']))->format('n') !== 10){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterOrderedAtYear' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'orderedAt' => [
							'year' => 2011
						]
					]
				],
				'includes' => ['orderedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if (((int)(new DateTime($item['orderedAt']))->format('Y')) !== 2011){
							return false;
						}
					}

					return true;
				}
			]
		];
	},


	'filterOrderedAtWithQuery' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$orderedAt = (new DateTime('2011-09-13 09:22:01'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'query' => (new DateTime('2011-09-13'))->format('m/d/Y')
				],
				'includes' => ['orderedAt']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderOrderedAt.id'),
					'orderedAt' => $orderedAt
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderOrderedAt.id');
					}),
					new ItemFieldsFilter(['orderedAt', 'id'], true)
				])
			]
		];
	},

	'createOrderDueDate:init' => function(Runtime $runtime) use (&$data){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$source = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);
		$year = (new DateTime('+3 years'))->format('Y');
		$data['createOrderDueDate.dueDate'] = (new DateTime($year.'-11-08 11:44:01'))->format(DateTime::ATOM);
		$source['dueDate'] = $data['createOrderDueDate.dueDate'];

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'auth' => 'customer',
				'body' => $source
			]
		];
	},

	'filterDueDate' => function(Runtime $runtime) use (&$data){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'dueDate' => (new DateTime($data['createOrderDueDate.dueDate']))->format('Y-m-d')
					]
				],
				'includes' => ['dueDate']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderDueDate.id'),
					'dueDate' => $data['createOrderDueDate.dueDate']
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderDueDate.id');
					}),
					new ItemFieldsFilter(['dueDate', 'id'], true)
				])
			]
		];
	},

	'createOrderForInspection:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$year = (new DateTime('+2 years'))->format('Y');
		$data['dueDate'] = (new DateTime($year.'-11-08 11:44:01'))->format(DateTime::ATOM);

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

	'accept2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForInspection.id').'/accept',
			]
		];
	},

	'scheduleInspection:init' => function(Runtime $runtime) use (&$data){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$data['scheduleInspection.scheduledAt'] = (new DateTime('+2 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForInspection.id').'/schedule-inspection',
				'body' => [
					'scheduledAt' => $data['scheduleInspection.scheduledAt'],
					'estimatedCompletionDate' => (new DateTime('+ 3 days'))->format(DateTime::ATOM),
				]
			]
		];
	},

	'filterInspectionScheduledAt' => function(Runtime $runtime) use (&$data){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'inspectionScheduledAt' =>
							(new DateTime($data['scheduleInspection.scheduledAt']))->format('Y-m-d'),
					]
				],
				'includes' => ['inspectionScheduledAt']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderForInspection.id'),
					'inspectionScheduledAt' => $data['scheduleInspection.scheduledAt']
				],
				'total' => ['>=', 1],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderForInspection.id');
					}),
					new ItemFieldsFilter(['inspectionScheduledAt', 'id'], true)
				])
			]
		];
	},

	'completeInspection:init' => function(Runtime $runtime) use (&$data){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$data['scheduleInspection.completedAt'] = (new DateTime('-1 days'))->format(DateTime::ATOM);

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrderForInspection.id').'/complete-inspection',
				'body' => [
					'completedAt' => $data['scheduleInspection.completedAt'],
					'estimatedCompletionDate' => (new DateTime('+ 4 days'))->format(DateTime::ATOM),
				]
			]
		];
	},

	'filterInspectionCompletedAt' => function(Runtime $runtime) use (&$data){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'inspectionCompletedAt'
							=> (new DateTime($data['scheduleInspection.completedAt']))->format('Y-m-d'),
					]
				],
				'includes' => ['inspectionCompletedAt']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderForInspection.id'),
					'inspectionCompletedAt' => $data['scheduleInspection.completedAt']
				],
				'total' => ['>=', 1],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderForInspection.id');
					}),
					new ItemFieldsFilter(['inspectionCompletedAt', 'id'], true)
				])
			]
		];
	},

	'createOrderWithPropertyLocation:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['property']['state'] = 'ID';
		$data['property']['zip'] = '88111';
		$data['property']['city'] = 'Some Cool City Which Is Unique';
		$data['property']['address1'] = '122 Welcome Street Which Is Unique';
		$data['property']['address2'] = '123 Welcome Street Which Is Unique';
		$data['property']['county'] = $runtime->getHelper()->county('LINCOLN', 'ID');

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

	'filterPropertyState' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'property' => [
							'state' => 'ID'
						]
					]
				],
				'includes' => ['property']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderWithPropertyLocation.id'),
					'property' => [
						'state' => [
							'code' => 'ID'
						]
					]
				],
				'total' => ['>=', 1],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderWithPropertyLocation.id');
					}),
					new ItemFieldsFilter(['property.state.code', 'id'], true)
				])
			]
		];
	},

	'filterPropertyCity' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'search' => [
						'property' => [
							'city' => 'cool City wHich'
						]
					]
				],
				'includes' => ['property']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderWithPropertyLocation.id'),
					'property' => [
						'city' => 'Some Cool City Which Is Unique'
					]
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderWithPropertyLocation.id');
					}),
					new ItemFieldsFilter(['property.city', 'id'], true)
				])
			]
		];
	},
	'filterPropertyZip' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'property' => [
							'zip' => '88111'
						]
					]
				],
				'includes' => ['property']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderWithPropertyLocation.id'),
					'property' => [
						'zip' => '88111'
					]
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderWithPropertyLocation.id');
					}),
					new ItemFieldsFilter(['property.zip', 'id'], true)
				])
			]
		];
	},
	'searchPropertyZip' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'search' => [
						'property' => [
							'zip' => '881'
						]
					]
				],
				'includes' => ['property']
			],
			'response' => [
				'assert' => function(Response $response){
					$data = $response->getBody();

					if (!$data){
						return false;
					}

					foreach ($data as $row){
						if (!starts_with($row['property']['zip'], '881')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},
	'filterPropertyAddress' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'search' => [
						'property' => [
							'address' => '123 welcome'
						]
					]
				],
				'includes' => ['property']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrderWithPropertyLocation.id'),
					'property' => [
						'address2' => '123 Welcome Street Which Is Unique'
					]
				],
				'total' => 1,
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('createOrderWithPropertyLocation.id');
					}),
					new ItemFieldsFilter(['property.address2', 'id'], true)
				])
			]
		];
	},

	'createOrderWithIsPaid1:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['isPaid'] = true;
		$data['paidAt'] = (new DateTime('2015-05-12'))->format(DateTime::ATOM);

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

	'createOrderWithIsPaid2:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['isPaid'] = true;
		$data['paidAt'] = (new DateTime('2014-12-12'))->format(DateTime::ATOM);

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

	'createOrderWithIsPaid3:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		$data['isPaid'] = true;
		$data['paidAt'] = (new DateTime('2013-08-12'))->format(DateTime::ATOM);

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


	'filterIsPaid' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'isPaid' => 'true'
					]
				],
				'includes' => ['isPaid']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['isPaid'] === false){
							return false;
						}
					}

					return true;
				}
			]
		];
	},


	'filterPaidAt' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => '2015-05-12'
					]
				],
				'includes' => ['paidAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['paidAt'] === null
							|| (new DateTime($item['paidAt']))->format('Y-m-d') !== '2015-05-12'){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterPaidAtDay' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'day' => '2013-08-12'
						]
					]
				],
				'includes' => ['paidAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['paidAt'] === null
							|| (new DateTime($item['paidAt']))->format('Y-m-d') !== '2013-08-12'){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterPaidAtFrom' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'from' => '2014-01-01'
						]
					]
				],
				'includes' => ['paidAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['paidAt'] === null
							|| new DateTime($item['paidAt']) < new DateTime('2014-01-01')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterPaidAtFromWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'from' => (new DateTime('+ 10 years'))->format('Y-m-d')
						]
					]
				]
			],
			'response' => [
				'body' => []
			]
		];
	},

	'filterPaidAtTo' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'to' => '2014-01-01'
						]
					]
				],
				'includes' => ['paidAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['paidAt'] === null
							|| new DateTime($item['paidAt']) > new DateTime('2014-01-01')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterPaidAtToWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'to' => (new DateTime('- 10 years'))->format('Y-m-d')
						]
					]
				],
			],
			'response' => [
				'body' => []
			]
		];
	},


	'filterPaidAtYear' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'year' => 2013
						]
					]
				],
				'includes' => ['paidAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['paidAt'] === null
							|| (new DateTime($item['paidAt']))->format('Y') != 2013){
							return false;
						}
					}

					return true;
				}
			]
		];
	},
	'filterPaidAtWithWrongYear' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'year' => 2000
						]
					]
				]
			],
			'response' => [
				'body' => []
			]
		];
	},

	'filterPaidAtMonth' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'paidAt' => [
							'month' => 12
						]
					]
				],
				'includes' => ['paidAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['paidAt'] === null
							|| (new DateTime($item['paidAt']))->format('n') != 12){
							return false;
						}
					}

					return true;
				}
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
	'filterPutOnHoldAt' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'putOnHoldAt' => (new DateTime())->format('Y-m-d')
					]
				],
				'includes' => ['putOnHoldAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['putOnHoldAt'] === null
							|| (new DateTime($item['putOnHoldAt']))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')){
							return false;
						}
					}

					return true;
				}
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

	'filterCompletedAt' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => (new DateTime())->format('Y-m-d')
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['completedAt'] === null
							|| (new DateTime($item['completedAt']))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterCompletedAtDay' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'day' => (new DateTime())->format('Y-m-d')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['completedAt'] === null
							|| (new DateTime($item['completedAt']))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterCompletedAtFrom' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'from' => (new DateTime('- 2 years'))->format('Y-m-d')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['completedAt'] === null
							|| new DateTime($item['completedAt']) < new DateTime('- 2 years')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterCompletedAtFromWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'from' => (new DateTime('+ 2 years'))->format('Y-m-d')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'body' => []
			]
		];
	},

	'filterCompletedAtTo' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'to' => (new DateTime('+ 4 years'))->format('Y-m-d')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['completedAt'] === null
							|| new DateTime($item['completedAt']) > new DateTime('+ 4 years')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterCompletedAtToWrong' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'to' => (new DateTime('- 2 years'))->format('Y-m-d')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'body' => []
			]
		];
	},


	'filterCompletedAtYear' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'year' => (new DateTime())->format('Y')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['completedAt'] === null
							|| (new DateTime($item['completedAt']))->format('Y') !== (new DateTime())->format('Y')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},
	'filterCompletedAtWithWrongYear' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'year' => 2012
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'body' => []
			]
		];
	},

	'filterCompletedAtMonth' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'month' => (new DateTime())->format('n')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['completedAt'] === null
							|| (new DateTime($item['completedAt']))->format('n') !== (new DateTime())->format('n')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterCompletedAtWithWrongMonth' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'completedAt' => [
							'month' => (new DateTime('-31 days'))->format('n')
						]
					]
				],
				'includes' => ['completedAt']
			],
			'response' => [
				'body' => []
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

	'filterRevisionReceivedAt' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'parameters' => [
					'filter' => [
						'revisionReceivedAt' => (new DateTime())->format('Y-m-d')
					]
				],
				'includes' => ['revisionReceivedAt']
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $item){
						if ($item['revisionReceivedAt'] === null
							|| (new DateTime($item['revisionReceivedAt']))->format('Y-m-d') !== (new DateTime())->format('Y-m-d')){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'filterByMultipleProcessStatuses' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$session->get('user.id').'/orders',
				'includes' => ['processStatus'],
				'parameters' => [
					'filter' => [
						'processStatus' => implode(',', [ProcessStatus::ACCEPTED, ProcessStatus::INSPECTION_COMPLETED])
					]
				]
			],
			'response' => [
				'assert' => function(Response $response){
					$data = $response->getBody();

					if (!$data){
						return false;
					}

					$statuses = [];

					foreach ($data as $row){
						if (!in_array($row['processStatus'], [ProcessStatus::ACCEPTED, ProcessStatus::INSPECTION_COMPLETED])){
							return false;
						}

						$statuses[] = $row['processStatus'];
					}

					if (!in_array(ProcessStatus::ACCEPTED, $statuses)
						|| !in_array(ProcessStatus::INSPECTION_COMPLETED, $statuses)){
						return false;
					}

					return true;
				}
			]
		];
	},
];