<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Filters\CompositeFilter;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Entities\Message;
use Ascope\QA\Support\Response;

$customer = uniqid('customer');

return [

	'createCustomer:init' => [
		'request' => [
			'url' => 'POST /customers',
			'body' => [
				'username' => $customer,
				'password' => 'password',
				'name' => $customer
			]
		]
	],
	'signinCustomer:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => $customer,
				'password' => 'password'
			]
		]
	],
	'invite:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/invitations',
				'body' => [
					'ascAppraiser' => 4
				],
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				]
			]
		];

	},
	'accept:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$session->get('user.id').'/invitations/'
					.$capture->get('invite.id').'/accept',
			]
		];
	},

	'addJobTypeFromCustomer:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/job-types',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'title' => 'Test 1'
				]
			]
		];
	},

	'addClientFromCustomer:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/clients',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'name' => 'Wonderful World'
				]
			]
		];
	},

	'createOrder1:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClientFromCustomer.id'),
			'clientDisplayedOnReport' => $capture->get('addClientFromCustomer.id')
		]);
		$data['jobType'] = $capture->get('addJobTypeFromCustomer.id');

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/appraisers/'
					.$session->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'body' => $data,
				'includes' => ['property']
			]
		];
	},

	'createOrder2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => $capture->get('addClientFromCustomer.id'),
			'clientDisplayedOnReport' => $capture->get('addClientFromCustomer.id')
		]);
		$data['jobType'] = $capture->get('addJobTypeFromCustomer.id');

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/appraisers/'
					.$session->get('user.id').'/orders',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'body' => $data,
				'includes' => ['property']
			]
		];
	},

	'validate' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/orders/'
					.$capture->get('createOrder2.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'content' => ' ',
				]
			],
			'response' => [
				'errors' => [
					'employee' => [
						'identifier' => 'required',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'create1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/orders/'
					.$capture->get('createOrder1.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'content' => 'Hello Appraiser!',
					'employee' => 'John Brown'
				],
				'includes' => ['order.property']
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'sender' => ['id' => $capture->get('createCustomer.id')],
					'content' => 'Hello Appraiser!',
					'employee' => 'John Brown',
					'order' => $capture->get('createOrder1'),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'isRead' => true
				],
				'filter' => new ItemFieldsFilter(['id', 'sender.id', 'content', 'order', 'createdAt', 'employee', 'isRead'], true)
			],
			'live' => [
				'body' => [
                    'channels' => [
                        'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                        'private-user-'.$capture->get('createCustomer.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                    ],
					'event' => 'order:send-message',
					'data' => [
						'id' => new Dynamic(Dynamic::INT),
						'sender' => ['id' => $capture->get('createCustomer.id')],
						'content' => 'Hello Appraiser!',
						'employee' => 'John Brown',
						'order' => new Dynamic(function($data) use ($capture) {
							return $data['id'] == $capture->get('createOrder1.id');
						}),
						'createdAt' => new Dynamic(Dynamic::DATETIME)
					]
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $data){
						return $data['event'] === 'order:send-message';
					}),
					new ItemFieldsFilter([
						'channels', 'event','data.id',
						'data.sender.id', 'data.content', 'data.order',
						'data.createdAt', 'data.employee'], true)
				])
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
								return starts_with($value, 'Message - Order on '.$capture->get('createOrder1.property.address1'));
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
								'name' => 'send-message'
							],
							'message' => new Dynamic(function($value) use ($capture){
								return str_contains($value, 'sent a message');
							}),
							'extra' => [
								'order' => $capture->get('createOrder1.id')
							]
						]
					]
				];
			}
		];
	},

	'get1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id')
					.'/messages/'.$capture->get('create1.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'includes' => ['order.property']
			],
			'response' => [
				'body' => $capture->get('create1')
			]
		];
	},

	'create2:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/orders/'
					.$capture->get('createOrder2.id').'/messages',

				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'content' => 'Hello Test Appraiser!',
					'employee' => 'John Black'
				],
				'includes' => ['order.property']
			]
		];
	},

	'create3' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/messages',
				'body' => [
					'content' => 'Hello Customer!'
				],
				'includes' => ['order.property']
			],
		];
	},

	'create4:init' => function(Runtime $runtime){
		$session = $runtime->getSession('appraiser');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder2.id').'/messages',
				'body' => [
					'content' => 'Hello Customer!'
				],
				'includes' => ['order.property']
			]
		];
	},

	'get1Read' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'body' => [
					'isRead' => true
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create1.id');
					}),
					new ItemFieldsFilter(['isRead'], true)
				])
			]
		];
	},

	'get2Read' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'body' => [
					'isRead' => true
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create2.id');
					}),
					new ItemFieldsFilter(['isRead'], true)
				])
			]
		];
	},

	'get3Unread' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'body' => [
					'isRead' => false
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create3.id');
					}),
					new ItemFieldsFilter(['isRead'], true)
				])
			]
		];
	},

	'get4Unread' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'body' => [
					'isRead' => false
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create4.id');
					}),
					new ItemFieldsFilter(['isRead'], true)
				])
			]
		];
	},

	'getAllRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'parameters' => [
					'filter' => [
						'isRead' => 'true'
					]
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $row){
						if ($row['isRead'] === false){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'getAllUnread' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'parameters' => [
					'filter' => [
						'isRead' => 'true'
					]
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'assert' => function(Response $response){
					$body = $response->getBody();

					if (!$body){
						return false;
					}

					foreach ($body as $row){
						if ($row['isRead'] === false){
							return false;
						}
					}

					return true;
				}
			]
		];
	},

	'markAsRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/messages/'
					.$capture->get('create3.id').'/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			]
		];
	},

	'get3Read' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'body' => [
					'isRead' => true
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create3.id');
					}),
					new ItemFieldsFilter(['isRead'], true)
				])
			]
		];
	},

	'againMarkAsRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/messages/'
					.$capture->get('create3.id').'/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			]
		];
	},

	'tryMarkAllAsRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/messages/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],

				'body' => [
					'messages' => [38888, 32313]
				]
			]
		];
	},

	'markAllAsRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$capture->get('createCustomer.id').'/messages/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'body' => [
					'messages' => [$capture->get('create4.id')]
				]
			]
		];
	},
	'get4Read' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			],
			'response' => [
				'body' => [
					'isRead' => true
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){
						return $v['id'] == $capture->get('create4.id');
					}),
					new ItemFieldsFilter(['isRead'], true)
				])
			]
		];
	},

	'updateCreatedAt:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'raw' => function(EntityManagerInterface $em) use ($capture){
				/**
				 * @var Message $m1
				 */
				$m1 = $em->find(Message::class, $capture->get('create1.id'));
				$m1->setCreatedAt(new DateTime('-10 days'));

				/**
				 * @var Message $m2
				 */
				$m2 = $em->find(Message::class, $capture->get('create2.id'));
				$m2->setCreatedAt(new DateTime('+10 days'));

				/**
				 * @var Message $m3
				 */
				$m3 = $em->find(Message::class, $capture->get('create3.id'));
				$m3->setCreatedAt(new DateTime('+40 days'));

				/**
				 * @var Message $m4
				 */
				$m4 = $em->find(Message::class, $capture->get('create4.id'));
				$m4->setCreatedAt(new DateTime('+50 days'));

				$em->flush();
			}
		];
	},

	'getAllFromOrder1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$m1 = $capture->get('create1');
		$m1['createdAt'] = new Dynamic(Dynamic::DATETIME);

		$m2 = $capture->get('create3');
		$m2['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m2['isRead'] = true;

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/orders/'
					.$capture->get('createOrder1.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'includes' => ['order.property']
			],
			'response' => [
				'body' => [$m2,$m1]
			]
		];
	},

	'getAllFromOrder2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$m1 = $capture->get('create2');
		$m1['createdAt'] = new Dynamic(Dynamic::DATETIME);

		$m2 = $capture->get('create4');
		$m2['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m2['isRead'] = true;

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/orders/'
					.$capture->get('createOrder2.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'includes' => ['order.property']
			],
			'response' => [
				'body' => [$m2,$m1]
			]
		];
	},

	'getAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$m1 = $capture->get('create1');
		$m1['createdAt'] = new Dynamic(Dynamic::DATETIME);

		$m2 = $capture->get('create2');
		$m2['createdAt'] = new Dynamic(Dynamic::DATETIME);

		$m3 = $capture->get('create3');
		$m3['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m3['isRead'] = true;

		$m4 = $capture->get('create4');
		$m4['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m4['isRead'] = true;


		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'includes' => ['order.property'],
			],
			'response' => [
				'body' => [$m4,$m3,$m2,$m1]
			]
		];
	},

	'delete2' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$capture->get('createCustomer.id').'/messages/'.$capture->get('create2.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			]
		];
	},
	'delete3' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$capture->get('createCustomer.id').'/messages/'.$capture->get('create3.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			]
		];
	},

	'getAllAfterDelete' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$m1 = $capture->get('create1');
		$m1['createdAt'] = new Dynamic(Dynamic::DATETIME);

		$m3 = $capture->get('create3');
		$m3['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m3['isRead'] = true;

		$m4 = $capture->get('create4');
		$m4['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m4['isRead'] = true;

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'includes' => ['order.property']
			],
			'response' => [
				'body' => [$m4,$m3,$m1]
			]
		];
	},

	'deleteAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'DELETE /customers/'.$capture->get('createCustomer.id').'/messages',
				'parameters' => [
					'messages' => $capture->get('create1.id')
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
			]
		];
	},

	'getAllAfterDeleteAll' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		$m3 = $capture->get('create3');
		$m3['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m3['isRead'] = true;

		$m4 = $capture->get('create4');
		$m4['createdAt'] = new Dynamic(Dynamic::DATETIME);
		$m4['isRead'] = true;

		return [
			'request' => [
				'url' => 'GET /customers/'.$capture->get('createCustomer.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinCustomer.token')
				],
				'includes' => ['order.property'],
			],
			'response' => [
				'body' => [$m4,$m3]
			]
		];
	},
];
