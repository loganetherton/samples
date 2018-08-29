<?php
use ValuePad\Tests\Integrations\Support\Runtime\Runtime;
use Ascope\QA\Integrations\Checkers\Dynamic;
use Ascope\QA\Support\Filters\ItemFieldsFilter;
use ValuePad\Core\Invitation\Enums\Requirement;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\Invitation\Entities\Invitation;
use Ascope\QA\Support\Filters\CompositeFilter;
use Ascope\QA\Support\Filters\FirstFilter;

return [
	'validateBefore' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => 9000000
				],
				'auth' => 'customer'
			],
			'response' => [
				'errors' => [
					'ascAppraiser' => [
						'identifier' => 'exists',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},
	'create1' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => 20,
					'requirements' => [Requirement::SAMPLE_REPORTS, Requirement::ACH]
				],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'reference' => new Dynamic(Dynamic::STRING),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'ascAppraiser' => [
						'id' => 20
					],
					'customer' => [
						'id' => $session->get('user.id')
					],
					'appraiser' => null,
					'status' => 'pending',
					'requirements' => [Requirement::SAMPLE_REPORTS, Requirement::ACH]
				],
				'filter' => new ItemFieldsFilter([
					'reference', 'createdAt', 'ascAppraiser.id', 'customer.id', 'appraiser', 'status', 'requirements'
				], true)
			]
		];
	},
	'create2:init' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => 21
				],
				'auth' => 'customer'
			]
		];
	},
	'validateAfter' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'POST /customers/'.$session->get('user.id').'/invitations',
				'body' => [
					'ascAppraiser' => 20
				],
				'auth' => 'customer'
			],
			'response' => [
				'errors' => [
					'ascAppraiser' => [
						'identifier' => 'already-invited',
						'message' => new Dynamic(Dynamic::STRING),
						'extra' => []
					]
				]
			]
		];
	},

	'get' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/invitations/'.$capture->get('create1.id'),
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'reference' => new Dynamic(Dynamic::STRING),
					'createdAt' => new Dynamic(Dynamic::DATETIME),
					'ascAppraiser' => [
						'id' => 20
					],
					'customer' => [
						'id' => $session->get('user.id')
					],
					'appraiser' => null,
					'status' => 'pending',
					'requirements' => [Requirement::SAMPLE_REPORTS, Requirement::ACH]
				],
				'filter' => new ItemFieldsFilter([
					'reference', 'createdAt', 'ascAppraiser.id', 'customer.id', 'appraiser', 'status', 'requirements'
				], true)
			]
		];
	},

	'getAll' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/invitations',
				'auth' => 'customer'
			],
			'response' => [
				'total' => ['>=', 1]
			]
		];
	},

	'updateCreatedAt:init' => function(Runtime $runtime){
		$capture = $runtime->getCapture();

		return [
			'raw' => function(EntityManagerInterface $em) use ($capture){
				/**
				 * @var Invitation $i2
				 */
				$i2 = $em->find(Invitation::class, $capture->get('create2.id'));
				$i2->setCreatedAt(new DateTime('-10 days'));

				/**
				 * @var Invitation $i1
				 */
				$i1 = $em->find(Invitation::class, $capture->get('create1.id'));
				$i1->setCreatedAt(new DateTime('+10 days'));

				$em->flush();
			}
		];
	},

	'getAllAsc' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/invitations',
				'parameters' => [
					'orderBy' => 'createdAt:asc',
					'perPage' => 1,
					 'filter' => [
					 	'licenseState' => $capture->get('create2.ascAppraiser.licenseState')
					 ],
				],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => $capture->get('create2.id')
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){ return true; }),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},

	'getAllDesc' => function(Runtime $runtime){
		$session = $runtime->getSession('customer');
		$capture = $runtime->getCapture();

		return [
			'request' => [
				'url' => 'GET /customers/'.$session->get('user.id').'/invitations',
				'parameters' => [
					'orderBy' => 'createdAt:desc',
					'perPage' => 1
				],
				'auth' => 'customer'
			],
			'response' => [
				'body' => [
					'id' => $capture->get('create1.id')
				],
				'filter' => new CompositeFilter([
					new FirstFilter(function($k, $v) use ($capture){ return true; }),
					new ItemFieldsFilter(['id'], true)
				])
			]
		];
	},
];
