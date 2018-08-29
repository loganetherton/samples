<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;

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
				'includes' => ['property'],
				'auth' => 'customer',
				'body' => $data
			]
		];
	},
	'acceptOrderByAppraiser:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/accept',
			]
		];
	},
	'workflowCompleted' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/completed',
				'auth' => 'customer'
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS
							&& $data['extra']['newProcessStatus'] == ProcessStatus::COMPLETED;
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
							'newProcessStatus' => ProcessStatus::COMPLETED
						]
					]
				]
			],
			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Completed - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},
	'getCompletedLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Completed".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::ACCEPTED,
						'newProcessStatus' => ProcessStatus::COMPLETED
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] === ProcessStatus::COMPLETED;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},
	'getCompleted' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus', 'completedAt']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'completed',
					'completedAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},

	'workflowReviewed' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/reviewed',
				'auth' => 'customer'
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS;
						}),
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
							'oldProcessStatus' => ProcessStatus::COMPLETED,
							'newProcessStatus' => ProcessStatus::REVIEWED
						]
					]
				],
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Reviewed - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getReviewedLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Reviewed".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::COMPLETED,
						'newProcessStatus' => ProcessStatus::REVIEWED
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::REVIEWED;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getReviewed' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'reviewed'
				]
			]
		];
	},

	'workflowRevisionInReview' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/revision-in-review',
				'auth' => 'customer'
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS;
						}),
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
							'oldProcessStatus' => ProcessStatus::REVIEWED,
							'newProcessStatus' => ProcessStatus::REVISION_IN_REVIEW
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Revision In Review - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getRevisionInReviewLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Revision In Review".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::REVIEWED,
						'newProcessStatus' => ProcessStatus::REVISION_IN_REVIEW
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::REVISION_IN_REVIEW;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},


	'getRevisionInReview' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'revision-in-review'
				]
			]
		];
	},

	'workflowRevisionPending' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/revision-pending',
				'auth' => 'customer'
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS;
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
							'oldProcessStatus' => ProcessStatus::REVISION_IN_REVIEW,
							'newProcessStatus' => ProcessStatus::REVISION_PENDING
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Revision Pending - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getRevisionPendingLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Revision Pending".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::REVISION_IN_REVIEW,
						'newProcessStatus' => ProcessStatus::REVISION_PENDING
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::REVISION_PENDING;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},


	'getRevisionPending' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'revision-pending'
				]
			]
		];
	},

	'workflowOnHold' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/on-hold',
				'auth' => 'customer',
				'body' => [
					'explanation' => 'I need to think.'
				]
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS
							&& $data['extra']['newProcessStatus'] === ProcessStatus::ON_HOLD;
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
							'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
							'newProcessStatus' => ProcessStatus::ON_HOLD,
							'explanation' => 'I need to think.'
						]
					],
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:send-message',
						'data' => new Dynamic(function($data){
							return is_array($data);
						})
					],
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'On Hold - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getPutOnHoldLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "On Hold".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::REVISION_PENDING,
						'newProcessStatus' => ProcessStatus::ON_HOLD,
						'explanation' => 'I need to think.'
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::ON_HOLD;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getOnHold' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus', 'comment', 'putOnHoldAt']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'on-hold',
					'comment' => 'I need to think.',
					'putOnHoldAt' => new Dynamic(Dynamic::DATETIME)
				]
			]
		];
	},

	'workflowOnHoldWithoutExplanation' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/on-hold',
				'auth' => 'customer',
				'body' => [
					'notifyAppraiser' => false
				]
			],
			'emails' => function(Runtime $runtime){
				return  [
					'body' => []
				];
			}
		];
	},

	'getOnHoldWithoutExplanation' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus', 'comment']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'on-hold',
					'comment' =>  null
				]
			]
		];
	},

	'workflowLate' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/late',
				'auth' => 'customer'
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS;
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
							'oldProcessStatus' => ProcessStatus::ON_HOLD,
							'newProcessStatus' => ProcessStatus::LATE
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Late - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			},

			'mobile' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');
				$capture = $runtime->getCapture();

				return [
					'body' => [
						[
							'users' => [$session->get('user.id')],
							'notification' => [
								'category' => 'order',
								'name' => 'update-process-status'
							],
							'message' => new Dynamic(function($value) use ($capture){
								return str_contains($value, ['"Late"']);
							}),
							'extra' => [
								'order' => $capture->get('createOrder.id'),
								'fileNumber' => $capture->get('createOrder.fileNumber'),
								'oldProcessStatus' => ProcessStatus::ON_HOLD,
								'newProcessStatus' => ProcessStatus::LATE
							]
						]
					]
				];
			}
		];
	},

	'getLateLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Late".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::ON_HOLD,
						'newProcessStatus' => ProcessStatus::LATE
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::LATE;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getLate' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'late'
				]
			]
		];
	},

	'workflowReadyForReview' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/ready-for-review',
				'auth' => 'customer'
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS;
						}),
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
							'oldProcessStatus' => ProcessStatus::LATE,
							'newProcessStatus' => ProcessStatus::READY_FOR_REVIEW
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Ready For Review - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getReadyForReviewLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Ready For Review".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::LATE,
						'newProcessStatus' => ProcessStatus::READY_FOR_REVIEW
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::READY_FOR_REVIEW;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getReadyForReview' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'ready-for-review'
				]
			]
		];
	},

	'workflowInspectionCompleted' => function(Runtime $runtime) use ($completedAt, $estimatedCompletionDate){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/inspection-completed',
				'auth' => 'customer',
				'body' => [
					'completedAt' => $completedAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			],
			'push' => [
				'body' => []
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS
							&& $data['extra']['newProcessStatus'] === ProcessStatus::INSPECTION_COMPLETED;
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
							'oldProcessStatus' => ProcessStatus::READY_FOR_REVIEW,
							'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
							'completedAt' => $completedAt,
							'estimatedCompletionDate' => $estimatedCompletionDate
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Inspection Completed - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getInspectionCompletedLogs' => function(Runtime $runtime) use ($completedAt, $estimatedCompletionDate){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Inspection Completed".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::READY_FOR_REVIEW,
						'newProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
						'completedAt' => $completedAt,
						'estimatedCompletionDate' => $estimatedCompletionDate
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::INSPECTION_COMPLETED;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},


	'getInspectionCompleted' => function(Runtime $runtime) use ($completedAt, $estimatedCompletionDate){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => [
					'inspectionCompletedAt',
					'estimatedCompletionDate',
					'processStatus'
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'inspectionCompletedAt' => $completedAt,
					'estimatedCompletionDate' => $estimatedCompletionDate,
					'processStatus' => 'inspection-completed'
				]
			]
		];
	},

	'workflowInspectionScheduled' => function(Runtime $runtime) use ($estimatedCompletionDate, $scheduledAt){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/inspection-scheduled',
				'auth' => 'customer',
				'body' => [
					'scheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate
				]
			],
			'push' => [
				'body' => []
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS
								&& $data['extra']['newProcessStatus'] === ProcessStatus::INSPECTION_SCHEDULED;
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
							'oldProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
							'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
							'scheduledAt' => $scheduledAt,
							'estimatedCompletionDate' => $estimatedCompletionDate
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Inspection Scheduled - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getInspectionScheduledLogs' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Inspection Scheduled".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::INSPECTION_COMPLETED,
						'newProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
						'scheduledAt' => $scheduledAt,
						'estimatedCompletionDate' => $estimatedCompletionDate
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::INSPECTION_SCHEDULED;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getInspectionScheduled' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => [
					'inspectionScheduledAt',
					'estimatedCompletionDate',
					'processStatus'
				]
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'inspectionScheduledAt' => $scheduledAt,
					'estimatedCompletionDate' => $estimatedCompletionDate,
					'processStatus' => 'inspection-scheduled'
				]
			]
		];
	},

	'workflowAccepted' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/accepted',
				'auth' => 'customer',
			],
			'push' => [
				'body' => []
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS
							&& $data['extra']['newProcessStatus'] === ProcessStatus::ACCEPTED;
						}),
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
							'newProcessStatus' => ProcessStatus::ACCEPTED,
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Accepted - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getAccepted' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'accepted'
				]
			]
		];
	},

	'getAcceptedLogs' => function(Runtime $runtime) use ($scheduledAt, $estimatedCompletionDate){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Accepted".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::INSPECTION_SCHEDULED,
						'newProcessStatus' => ProcessStatus::ACCEPTED
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['user']['type'] == 'customer'
							&& $v['extra']['newProcessStatus'] == ProcessStatus::ACCEPTED;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'workflowRequestForBid' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/request-for-bid',
				'auth' => 'customer',
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS;
						}),
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
							'newProcessStatus' => ProcessStatus::REQUEST_FOR_BID,
						]
					]
				]
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'Request For Bid - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getRequestForBidLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "Request For Bid".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::ACCEPTED,
						'newProcessStatus' => ProcessStatus::REQUEST_FOR_BID
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::REQUEST_FOR_BID;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

	'getRequestForBid' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => 'request-for-bid'
				]
			]
		];
	},

	'workflowNew' => function(Runtime $runtime) {
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder.id').'/workflow/new',
				'auth' => 'customer',
			],
			'live' => [
				'body' => [
					[
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => new Dynamic(function($data){
							return $data['action'] === Action::UPDATE_PROCESS_STATUS;
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
							'oldProcessStatus' => ProcessStatus::REQUEST_FOR_BID,
							'newProcessStatus' => ProcessStatus::FRESH,
						]
					]
				],
			],

			'emails' => function(Runtime $runtime){
				$session = $runtime->getSession('appraiser');

				$capture = $runtime->getCapture();

				return  [
					'body' => [
						[
							'from' => [
								'no-reply@valuepad.com' => 'The ValuePad Team'
							],
							'to' => [
								$session->get('user.email') => $session->get('user.firstName')
									.' '.$session->get('user.lastName'),
							],
							'subject' => new Dynamic(function($value) use ($capture){
								return starts_with($value, 'New - Order on '.$capture->get('createOrder.property.address1'));
							}),
							'contents' => new Dynamic(Dynamic::STRING)
						]
					]
				];
			}
		];
	},

	'getNew' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/orders/'.$capture->get('createOrder.id'),
				'auth' => 'customer',
				'includes' => ['processStatus']
			],
			'response' => [
				'body' => [
					'id' => $capture->get('createOrder.id'),
					'fileNumber' => $capture->get('createOrder.fileNumber'),
					'processStatus' => ProcessStatus::FRESH
				]
			]
		];
	},

	'getNewLogs' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$capture->get('createOrder.id').'/logs',
				'parameters' => [
					'perPage' => 1000,
				]
			],
			'response' => [
				'body' => [
					'action' => Action::UPDATE_PROCESS_STATUS,
					'message' => sprintf(
						'%s has changed the process status to "New".',
						$customerSession->get('user.name')
					),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'oldProcessStatus' => ProcessStatus::REQUEST_FOR_BID,
						'newProcessStatus' => ProcessStatus::FRESH
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v){
						return $v['action'] === Action::UPDATE_PROCESS_STATUS
							&& $v['extra']['newProcessStatus'] == ProcessStatus::FRESH;
					}),
					new ItemFieldsFilter(['action', 'extra', 'message'], true)
				])
			]
		];
	},

];