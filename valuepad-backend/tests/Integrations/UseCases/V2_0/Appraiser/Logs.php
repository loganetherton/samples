<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use ValuePad\Tests\Integrations\Fixtures\OrdersFixture;
use Ascope\QA\Integrations\Checkers\Dynamic;
use ValuePad\Core\Log\Enums\Action;
use Ascope\QA\Support\Filters\FirstFilter;

return [
	'createOrder1:init' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'includes' => ['property'],
				'auth' => 'customer',
				'body' => OrdersFixture::get($runtime->getHelper(), [
					'client' => 1,
					'clientDisplayedOnReport' => 2
				])
			]
		];
	},
	'createOrder2' => function(Runtime $runtime){
		$customerSession = $runtime->getSession('customer');
		$appraiserSession = $runtime->getSession('appraiser');
		$data = OrdersFixture::get($runtime->getHelper(), [
			'client' => 1,
			'clientDisplayedOnReport' => 2
		]);

		return [
			'request' => [
				'url' => 'POST /customers/'
					.$customerSession->get('user.id').'/appraisers/'
					.$appraiserSession->get('user.id').'/orders',
				'includes' => ['property', 'customer'],
				'auth' => 'customer',
				'body' => $data
			],
			'live' => function(Runtime $runtime){
				$customerSession = $runtime->getSession('customer');

				$capture = $runtime->getCapture();
				$order = $capture->get('createOrder2');

				return [
					'body' => [
                        'channels' => [
                            'private-user-'.$runtime->getSession('appraiser')->get('user.id'),
                            'private-user-'.$runtime->getSession('customer')->get('user.id').'-as-'.$runtime->getSession('appraiser')->get('user.id')
                        ],
						'event' => 'order:create-log',
						'data' => [
							'id' => new Dynamic(Dynamic::INT),
							'action' => Action::CREATE_ORDER,
							'actionLabel' => 'New Order',
							'message' => sprintf(
								'You have received a new order on %s, %s, %s %s from %s.',
								$order['property']['address1'],
								$order['property']['city'],
								$order['property']['state']['code'],
								$order['property']['zip'],
								$order['customer']['name']

							),
							'user' => new Dynamic(function($data) use ($customerSession){
								return $data['id'] == $customerSession->get('user.id') && $data['type'] == 'customer';
							}),
							'order' => new Dynamic(function($data) use ($capture){
								return $data['id'] == $capture->get('createOrder2.id');
							}),
							'extra' => [
								'user' => $customerSession->get('user.name'),
								'customer' => $order['customer']['name'],
								'address1' => $capture->get('createOrder2.property.address1'),
								'address2' => $capture->get('createOrder2.property.address2'),
								'city' => $capture->get('createOrder2.property.city'),
								'zip' => $capture->get('createOrder2.property.zip'),
								'state' => $capture->get('createOrder2.property.state'),
							],
							'createdAt' => new Dynamic(Dynamic::DATETIME)
						],
					],
					'filter' => new FirstFilter(function($k, $data){
						return $data['event'] === 'order:create-log';
					})
				];
			}
		];
	},

	'getAllByAppraiser' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		$capture = $runtime->getCapture();

		$order = $capture->get('createOrder2');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/logs',
				'parameters' => [
					'perPage' => 1000
				]
			],
			'response' => [
				'body' => [
					'id' => new Dynamic(Dynamic::INT),
					'action' => Action::CREATE_ORDER,
					'actionLabel' => 'New Order',
					'message' => sprintf(
						'You have received a new order on %s, %s, %s %s from %s.',
						$order['property']['address1'],
						$order['property']['city'],
						$order['property']['state']['code'],
						$order['property']['zip'],
						$order['customer']['name']

					),
					'user' => new Dynamic(function($data) use ($customerSession){
						return $data['id'] == $customerSession->get('user.id') && $data['type'] == 'customer';
					}),
					'order' => new Dynamic(function($data) use ($capture){
						return $data['id'] == $capture->get('createOrder1.id');
					}),
					'extra' => [
						'user' => $customerSession->get('user.name'),
						'customer' => $order['customer']['name'],
						'address1' => $capture->get('createOrder1.property.address1'),
						'address2' => $capture->get('createOrder1.property.address2'),
						'city' => $capture->get('createOrder1.property.city'),
						'zip' => $capture->get('createOrder1.property.zip'),
						'state' => $capture->get('createOrder1.property.state'),
					],
					'createdAt' => new Dynamic(Dynamic::DATETIME)
				],
				'filter' => new FirstFilter(function($k, $v) use ($capture){
					return $v['order']['id'] == $capture->get('createOrder1.id');
				}),
			]
		];
	},

	'getAllByOrder' => function(Runtime $runtime){
		$appraiserSession = $runtime->getSession('appraiser');
		$customerSession = $runtime->getSession('customer');

		$capture = $runtime->getCapture();
		$order = $capture->get('createOrder2');

		return [
			'request' => [
				'url' => 'GET /appraisers/'.$appraiserSession->get('user.id').'/orders/'.$order['id'].'/logs',
			],
			'response' => [
				'body' => [
					[
						'id' => new Dynamic(Dynamic::INT),
						'action' => Action::CREATE_ORDER,
						'actionLabel' => 'New Order',
						'message' => sprintf(
							'You have received a new order on %s, %s, %s %s from %s.',
							$order['property']['address1'],
							$order['property']['city'],
							$order['property']['state']['code'],
							$order['property']['zip'],
							$order['customer']['name']

						),
						'user' => new Dynamic(function($data) use ($customerSession){
							return $data['id'] == $customerSession->get('user.id') && $data['type'] == 'customer';
						}),
						'order' => new Dynamic(function($data) use ($capture){
							return $data['id'] == $capture->get('createOrder2.id');
						}),
						'extra' => [
							'user' => $customerSession->get('user.name'),
							'customer' => $order['customer']['name'],
							'address1' => $capture->get('createOrder2.property.address1'),
							'address2' => $capture->get('createOrder2.property.address2'),
							'city' => $capture->get('createOrder2.property.city'),
							'zip' => $capture->get('createOrder2.property.zip'),
							'state' => $capture->get('createOrder2.property.state'),
						],
						'createdAt' => new Dynamic(Dynamic::DATETIME)
					]
				]
			]
		];
	}
];
