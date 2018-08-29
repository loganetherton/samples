<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Tests\Integrations\Fixtures\AppraisersFixture;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Appraisal\Entities\Message;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;
use Ascope\QA\Support\Response;

return [

	'createW9:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.pdf'
			]
		]
	],

	'createEoDocument:init' => [
		'request' => [
			'url' => 'POST /documents',
			'files' => [
				'document' => __DIR__.'/test.txt'
			]
		]
	],


	'createAppraiser:init' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		$data = AppraisersFixture::get([
			'username' => 'appraisertestmessages',
			'password' => 'password',
			'w9' => [
				'id' => $capture->get('createW9.id'),
				'token' => $capture->get('createW9.token')
			],
			'qualifications' => [
				'primaryLicense' => [
					'number' => 'dummy',
					'state' => 'TX'
				],
			],
			'eo' => [
				'document' => [
					'id' => $capture->get('createEoDocument.id'),
					'token' => $capture->get('createEoDocument.token')
				]
			]
		]);

		return [
			'request' => [
				'url' => 'POST /appraisers',
				'includes' => ['qualifications'],
				'body' => $data
			]
		];
	},

	'getAscAppraiser:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /asc',
				'parameters' => [
					'search' => [
						'licenseNumber' => $capture->get('createAppraiser.qualifications.primaryLicense.number')
					],
					'filter' => [
						'licenseState' => $capture->get('createAppraiser.qualifications.primaryLicense.state.code')
					]
				]
			]
		];
	},

	'invite:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => $capture->get('getAscAppraiser.0.id')
				],
				'auth' => 'customer',
			]
		];
	},

	'signinAppraiser:init' => [
		'request' => [
			'url' => 'POST /sessions',
			'body' => [
				'username' => 'appraisertestmessages',
				'password' => 'password'
			]
		]
	],

	'acceptInvitation:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'
					.$capture->get('createAppraiser.id').'/invitations/'
					.$capture->get('invite.id').'/accept',
				'auth' => 'guest',
				'headers' => [
					'Token' => $capture->get('signinAppraiser.token')
				]
			],
		];
	},


	'createOrder1:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},

	'createOrder2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/appraisers/'
					.$capture->get('createAppraiser.id').'/orders',
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},
	'create1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
					.$capture->get('createOrder1.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'content' => 'Hello Appraiser!'
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'sender' => ['id' => $capture->get('createAppraiser.id')],
					'content' => 'Hello Appraiser!',
					'order' => $capture->get('createOrder1'),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'isRead' => true,
				],
				'filter' => new ItemFieldsFilter(['id', 'sender.id', 'content', 'order', 'createdAt', 'isRead'], true)
			],
			'push' => [
				'body' => [
					[
						'type' => 'order',
						'event' => 'send-message',
						'order' => $capture->get('createOrder1.id'),
						'message' => new Dynamic(Dynamic::INT)
					]
				]
			],
			'live' => [
				'body' => [
                    'channels' => [
                        'private-user-'.$capture->get('createAppraiser.id'),
                        'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$capture->get('createAppraiser.id')
                    ],
					'event' => 'order:send-message',
					'data' => [
						'id' => new Dynamic(Dynamic::INT),
						'sender' => ['id' => $capture->get('createAppraiser.id')],
						'content' => 'Hello Appraiser!',
						'order' => $capture->get('createOrder1'),
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
			'emails' => [
				'body' => []
			],
			'mobile' => [
				'body' => []
			]
		];
	},

	'get1' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id')
					.'/messages/'.$capture->get('create1.id'),
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
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
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
					.$capture->get('createOrder2.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
				'body' => [
					'content' => 'Hello Appraiser!'
				]
			]
		];
	},
	'create3:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder1.id').'/messages',

				'auth' => 'customer',

				'body' => [
					'content' => 'Hello Test Appraiser!',
					'employee' => 'John Black'
				]
			]
		];
	},

	'create4:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/orders/'
					.$capture->get('createOrder2.id').'/messages',

				'auth' => 'customer',

				'body' => [
					'content' => 'Hello Test Appraiser!',
					'employee' => 'John Black'
				]
			]
		];
	},

	'get1Read' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'parameters' => [
					'filter' => [
						'isRead' => 'true'
					]
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'parameters' => [
					'filter' => [
						'isRead' => 'true'
					]
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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

	'getTotal1' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages/total',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
			],
			'response' => [
				'body' => [
					'total' => 4,
					'unread' => 2
				]
			]
		];
	},


	'markAsRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/messages/'
					.$capture->get('create3.id').'/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
			]
		];
	},

	'get3Read' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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

	'getTotal2' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages/total',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
			],
			'response' => [
				'body' => [
					'total' => 4,
					'unread' => 1
				]
			]
		];
	},

	'againMarkAsRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/messages/'
					.$capture->get('create3.id').'/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
			]
		];
	},

	'tryMarkAllAsRead' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/messages/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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
				'url' => 'POST /appraisers/'.$capture->get('createAppraiser.id').'/messages/mark-as-read',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
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

	'getTotal3' => function(Runtime $runtime){

		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages/total',
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
			],
			'response' => [
				'body' => [
					'total' => 4,
					'unread' => 0
				]
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
					.$capture->get('createOrder1.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/orders/'
					.$capture->get('createOrder2.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
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
				'url' => 'GET /appraisers/'.$capture->get('createAppraiser.id').'/messages',
				'parameters' => [
					'orderBy' => 'createdAt:desc'
				],
				'auth' => 'guest',
				'headers' => [
					'token' => $capture->get('signinAppraiser.token')
				],
			],
			'response' => [
				'body' => [$m4,$m3,$m2,$m1]
			]
		];
	},
];
